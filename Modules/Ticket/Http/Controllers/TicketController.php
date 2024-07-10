<?php

namespace Modules\Ticket\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Ticket\DataTables\AdminSupportDataTable;
use App\Http\Controllers\EmailController;
use App\Exports\TicketsExport;
use DB;
use Excel;

use Modules\Ticket\Http\Models\{
    Thread,
    ThreadReply,
    ThreadStatus,
    Priority,
    Department
};
use Illuminate\Support\Facades\{
    Auth,
    Session
};
use Modules\Ticket\Http\Requests\{
    AdminTicketRequest,
    ReplyRequest,
    TicketUpdateRequest
};

use App\Models\{
    User,
    Preference
};

use App\Services\Mail\{
    AssigneeMailService,
    TicketReplyMailService,
    VendorMailService
};


class TicketController extends Controller
{


    /**
     * Ticket list for admin
     * @param AdminSupportDataTable $adminSupportDataTable
     *
     * @return [type]
     */
    public function index(AdminSupportDataTable $adminSupportDataTable)
    {
        $data['status']    = ThreadStatus::get();
        $data['departments'] = Department::get();
        $data['assignees'] = User::has('threads')->with('threads:id,assigned_member_id')->distinct()->get(['id', 'name']);
        $data['from']        = $from        = request('from');
        $data['to']          = $to          = request('to');
        $data['allstatus']   = $allstatus   = request('status');
        $data['alldepartment'] = $alldepartment = request('department_id');
        $data['allassignee'] = $allassignee = request('assignee');
        $data['summary']     = $summary = (new Thread())->getThreadSummary($from, $to, $allstatus, $alldepartment, null, $allassignee);
        if ((isset($from) && !empty($from)) || (isset($to) && !empty($to)) || (isset($alldepartment) && !empty($alldepartment))) {
            $data['exceptClickedStatus'] = $exceptClickedStatus = (new Thread())->getExceptClickedStatus($allstatus);
            if (!empty($data['exceptClickedStatus'])) {
                foreach ($summary as $key => $summ) {
                    foreach ($exceptClickedStatus as $key => $exceptClickedSts) {
                        if ($exceptClickedSts->name == $summ->name) {
                            $summ->total_status = $exceptClickedSts->total_status;
                        }
                    }
                }
            }
        } else {
            $data['filteredStatus'] = $filteredStatus = (new Thread())->getFilteredStatus(['from' => $from, 'to' => $to, 'allstatus' => $allstatus, 'alldepartment' => $alldepartment, 'allassignee' => $allassignee]);
            if (!empty($data['filteredStatus'])) {
                foreach ($summary as $key => $summ) {
                    foreach ($filteredStatus as $key => $filtered) {
                        if ($filtered->name == $summ->name) {
                            $summ->total_status = $filtered->total_status;
                        }
                    }
                }
            }
        }
        return $adminSupportDataTable->render('ticket::admin_index', $data);
    }

    /**
     * Thred Details
     * @param mixed $id
     *
     * @return [view]
     */
    public function view($id)
    {
        $ticket_id   = base64_decode($id);
        $data['ticketStatuses']     = ThreadStatus::getAll();
        $data['ticketDetails']      = (new Thread)->getAllTicketDetailsById($ticket_id);

        if (empty($data['ticketDetails'])) {
            return redirect()->back()->with('fail', __('The data you are trying to access is not found.'));
        }
        $data['priority']           = Priority::where('id', '!=', $data['ticketDetails']->priority_id)->get();
        $data['ticketReplies']      = (new Thread)->getAllTicketRepliersById($ticket_id);
        $data['ticketStatus'] = ThreadStatus::where('id', '=',  $data['ticketDetails']->threadStatus->id)->orderBy('name')->first();
        $data['filePath'] = "public/uploads";
        $data['assignee']    = User::whereHas('roleUser', function ($query) {
            $query->where('role_id', 1);
        })->active()->get();
        return view('ticket::admin_reply', $data);
    }

    /**
     * Ticket add
     * @return [view]
     */
    public function add()
    {
        $data['object_type'] = 'TICKET';
        $data['priorities']   = Priority::get();
        $data['assignees']    = User::whereHas('roleUser', function ($query) {
            $query->where('role_id', 1);
        })->active()->get();

        $data['users']    = User::whereHas('roleUser', function ($query) {
            $query->where('role_id', 2); // 2 refered to vendor
        })->active()->get();

        $data['ticketStatus'] = ThreadStatus::get();
        $data['departments']  = Department::get();
        $data['customers']    = User::where('status', 'active')->get();
        return view('ticket::admin.add', $data);
    }



    /**
     * Store Ticket
     * @param AdminTicketRequest $request
     *
     */
    public function store(AdminTicketRequest $request)
    {
        try {
            DB::beginTransaction();
            $data['receiver_id']        = $request->receiver_id;
            $data['email']              = $request->email ??  null;
            $data['name']               = isset($request->to) ? $request->to : null;
            $data['department_id']      = $request->department_id;
            $data['priority_id']        = $request->priority_id;
            $data['thread_status_id']   = $request->status_id;
            $data['thread_key']         = 'THRD-' . uniqid();
            $data['subject']            = $request->subject;
            $data['thread_type']        = $request->object_type;
            $data['sender_id']          = Auth::user()->id;
            $data['date']               = date('Y-m-d H:i:s');
            $data['project_id']         = isset($request->project_id) ?  $request->project_id : null;
            $data['last_reply']         = date('Y-m-d H:i:s');
            $data['assigned_member_id'] = $request->assign_id;

            // Creating new thread
            $id = (new Thread)->store($data);

            $objectType = '';
            // creating a initial thread reply if the thread is created
            if (!empty($id)) {
                $replyData['thread_id'] = $id;
                $replyData['receiver_id']   = Auth::user()->id;
                $replyData['message']   = $request->message;
                $replyData['date']      = $data['date'];
                $replyData['has_attachment']      = isset($request->file) ? 1 : 0;
                $objectType = (new ThreadReply)->store($replyData);
            }
            DB::commit();
            $attachments = [];
            if (isset($request->file_id) && !empty($request->file_id)) {
                $fileId = ThreadReply::where('id', $id)->get();

                foreach ($fileId as $key => $file) {
                    $attachments = $file->filesUrlNew(['imageUrl' => 'true']);

                }
            }
            $info['emailInfo'] = (new Thread())->getAllTicketDetailsById($id);
            $info['assignId'] = $request->assign_id;
            $info['receiverId'] = $request->receiver_id;
            $info['files'] = $attachments;

            if ($request->assign_id) {
                $emailResponse = (new AssigneeMailService())->send($info);
                if ($emailResponse['status'] == false) {
                    \Session::flash('fail', __($emailResponse['message']));
                }
            }
            // Mail to vendor
            if ($request->receiver_id) {
                $emailResponse = (new VendorMailService())->send($info);
                if ($emailResponse['status'] == false) {
                    \Session::flash('fail', __($emailResponse['message']));
                }
            }
            Session::flash('success', __('Successfully Saved'));
            return redirect('admin/ticket/reply/'.base64_encode($id));
        } catch (\Exception $e) {
            DB::rollBack();
            return bacopienas administratoram","3K7pgV":"Ziņu sūtīšanas iespēja pieejama tikai {=m2}","1BMn5k":"kopienas administratoriem","3qZmoO":"Pārejiet uz {=m2}, lai turpinātu sarunu","1XoSRG":"šo saraksti",KMHQO:"Šī kopiena tika deaktivizēta. Vairs nevarat tajā sūtīt ziņas.","2ncNMF":"Šī kopiena tika deaktivizēta","3gjAA2":"WhatsApp atbalsts","9wFxn":"Dzēst grupu man","3Trrkr":"Šī grupa vairs nav pieejama","19kBBZ":"Šī kopiena vairs nav pieejama. {=m2}",Eltzp:"Uzzināt vairāk","1Q3Etl":"Šī grupa vairs nav pieejama","1MmBvO":"Vienumu, ko mēģinājāt pievienot, neizdevās ielādēt.",MlINv:"Ziņu nevar rediģēt","3h5KZB":"Rakstiet ziņu","3ofbOg":"Aizvērt paneli",NE5Dz:"Atvērt GIF paneli","1MfILW":"Atvērt uzlīmju paneli","25FkwM":"Sūtīt","393mz4":"Balss zvans","1q9mNK":"Video zvans","3KsdlE":"Veikals","3t362Y":"Katalogs","4vOQrI":"Ziņu sūtīšana iespējama tikai administratoriem","1o6LSN":"Tikai administratori var sūtīt ziņas","1msmqG":"Paziņojumu grupa",YC2Xn:"{community}",eJXQx:"Piešķirta jums","2nzwVN":"Piešķirta: {agentName}","1A7Ci4":"Ieslēgt skaņu","4zbVSn":"Izslēgt skaņu","22n3V4":"Profila informācija",liGkN:"Ziņu saraksts. Lai atvērtu ziņu kontekstizvēlni, nospiediet labo bulttaustiņu uz ziņas.","4FnAYZ":"Ziņu saraksts. Lai atvērtu ziņu kontekstizvēlni, nospiediet kreiso bulttaustiņu uz ziņas.","4pqtaj":"Šis video tiek atskaņots attēls-attēlā režīmā.","3e9NPU":"Skatīt",J2Yam:"Aizdomīga saite","2GJELE":"WhatsApp Web neatbalsta zvanus. Lai piezvanītu šim uzņēmumam, atveriet WhatsApp savā tālrunī.","30iXXv":"Skatīt nosūtīto grozu","1y3IbA":"Skatīt saņemto grozu","1aBrnE":"Nevarēja nosūtīt šo ziņu.","1g3L8P":"Nevarēja pārsūtīt šo ziņu, jo fails vairs nav atrodams tālrunī.","2Reha":"Radās kļūda. Noklikšķiniet, lai uzzinātu vairāk.","2vD411":"Jūsu ziņa netika nosūtīta.",K3Fmi:{"*":"Jūsu {reactions} reakcijas netika nosūtītas",_1:"Jūsu {reactions} reakcija netika nosūtīta"},"1XXW43":"Jūsu mainītais balsojums netika nosūtīts.","1EH9Vx":"Jūsu balsojums netika nosūtīts.","1NK0CQ":"Could not pin.","1oFfnr":"Could not unpin.",W3IoR:"Darbības, ko veicāt ar šo ziņu, netika nosūtītas.",Pqk5j:"Jūsu ziņa un ar ziņu veiktās darbības netika nosūtītas.","2UMMxA":"Veikali","2Nqnnt":"+{number}","3217yH":"Kanāla uzaicinājuma saite","4dwzXQ":"{progress}% ({eta})","3uhcjZ":"Atvērt attēlu","4Ah4bt":"Skatīt informāciju",aJwEg:"Pārskatīt un maksāt","2OPqXz":"Pasūtījumus nevar skatīt lietotnē WhatsApp Web","2FMFfW":"Lai skatītu šo pasūtījumu, izmantojiet lietotni WhatsApp mobilajā ierīcē.",NEWkE:"Apmaksāts","1692RT":"Kopā",sLf0F:{4:"{number-of-items} prece","*":"{number-of-items} preces",_1:"1 prece"},"1KSfSo":"Statusa atjauninājums nav atrasts","4r20Bx":"Citēta ziņa",SBkDD:"Fotoattēls","2SOGaJ":"Video","1x9ZOx":"Daudzums: {order_items_quantity} • {order_total_price}","3rTXIk":"Gaida",xgkjk:"Nosūtīta","2VFqLn":"Piegādāta","45eIFk":"Izlasīta","1iTWkT":"Skatīt uzaicinājumu","1TFgKg":"Uzaicinājumam beidzies derīguma termiņš","22A8O7":"Lejupielādēt visu","1iWm4D":"Pārsūtīt visu","3XG8TV":"Noņemt visas atzīmes",k3tpf:"Atzīmēt visu","4FG8uO":"Dzēst visu","4DjgJi":"Pārsūtīt multividi",DNiv2:"Grupa","4gjFRX":"Statuss","2obeBs":"Iespējams, {name}",Yi0GJ:"Kopienas administrators","3bM8te":"Gaida šo ziņu. Pārbaudiet tālruni.","2jZa3c":"This message can't be displayed here. Please open WhatsApp on your phone to view the message.","4wKJPR":"Kā dalībnieks varat pievienoties kopienas grupām un saņemt jaunumus no administratoriem","1GPcqk":"Jūsu profils ir redzams administratoriem","3beO71":"Ikviens grupas dalībnieks tagad ir kopienas dalībnieks","24ZmC7":"Dalībnieki var pievienoties šai grupai un citām kopienas grupām.","1SHqMs":"Pārvaldīt kopienu","3byR91":"Izpētīt kopienu","1j1yKU":"Jūs pievienojāt šo grupu kopienai {community}","31lIy8":'{author} pievienoja šo grupu kopienai "{community}"',Tdp2w:'Šī grupa tika pievienota kopienai "{community}"',"1kZEmr":"Jūs pievienojāt šo grupu kopienai","4CnqQO":"{author} pievienoja šo grupu kopienai","1MRCkL":"Šī grupa tika pievienota kopienai","31mOeV":"Kā dalībnieks varat pievienoties kopienas grupām un saņemt jaunumus no administratoriem","1c4D1i":"Jūsu profils ir redzams administratoriem","3ZYcad":"Izpētīt kopienu","180YAa":'{user_name} pievienoja jūs šai kopienas grupai: "{community}"',"2KbCx2":"Izmantojot ielūgumu, jūs pievienojāties kopienas grupai: {community}","2WVYcE":"{user_name} pievienoja jūs kopienas grupai",bff9y:"Izmantojot ielūgumu, jūs pievienojāties kopienas grupai","3GS6WH":"Nosūtiet svarīgus jaunumus no administratoriem visiem kopienas dalībniekiem vienlaikus.","3U02n7":"Pārvaldīt kopienu",Dgmx9:"Laipni lūdzam jūsu kopienā!","1ayxRT":"Jūs pievienojāties šai kopienai","152dnq":"{author} pievienoja jūs","3X4Ctm":"Administratori šeit sūtīs visiem dalībniekiem svarīgus paziņojumus par kopienu","3wdWAa":"Skatīt kopienas informāciju","2aB3i":"Laipni lūdzam kopienā!",fMYKO:"Lai to skatītu, iegūstiet jaunāko lietotnes WhatsApp versiju.",MdRCZ:"Atjaunināt WhatsApp","3LRhTn":"Pārsūtīta vairākas reizes",VlL2I:"Pārsūtīta","27k9Gu":"Jūs atiestatījāt šīs grupas uzaicinājuma saiti. Noklikšķiniet, lai skatītu jauno uzaicinājuma saiti.","16hyEI":"Veicāt izmaiņas grupas iestatījumos, bloķējot ziņas, kuras ir tikušas pārsūtītas vairākas reizes šajā grupā.","3Ib1Hu":"Veicāt izmaiņas grupas iestatījumos, atļaujot ziņas, kuras ir tikušas pārsūtītas vairākas reizes.",O3Va5:"Šajā grupā nosūtīt ziņas atļauts tikai administratoriem.",FzeN7:"Ir mainījušies grupas dalībnieki. Noklikšķiniet, lai skatītu.",kGh3d:"Uzaicinājums, izmantojot saiti, kļuva nepieejams. Noklikšķiniet, lai uzzinātu vairāk.","1TFNSV":"Uzaicinājums, izmantojot saiti, atkal ir pieejams, Noklikšķiniet, lai skatītu jauno uzaicinājuma saiti.","28BLkP":"Izgaistošo ziņu funkcija tagad ļauj arī paturēt ziņas sarakstē. Noklikšķiniet, lai uzzinātu vairāk.","4zXYWL":"Jūs vairs neesat administrators. Šo iestatījumu var mainīt tikai administratori.","2F8wlc":"Atjauniniet lietotni WhatsApp uz jaunāko versiju, lai mainītu šo iestatījumu.","1IQjl7":"Jūs vairs neesat administrators. Tikai administratori var pārskatīt pievienošanās pieprasījumus.","4DT7JA":"Atjauniniet lietotni WhatsApp uz jaunāko versiju, lai pārskatītu šo pieprasījumu.","2a1tDk":"Jūs vairs neesat administrators. Šo iestatījumu var mainīt tikai administratori","44ji9D":"Skatiet preces",ww6M:"Reāllaika vieta šajā ierīcē nav pieejama. Skatiet atrašanās vietu savā tālrunī.","2eHVJY":"Reāllaika vieta nav pieejama",uqtIq:"Atzīmētās ziņas","4p3B3Z":"Paturēta ziņa","28mPJl":"Piesprausta ziņa","3zo6HQ":"Rediģēta","2NeQbo":"Skatīt visu","2H91y3":"{name} veica tālruņa numura maiņu uz citu. Noklikšķiniet, lai rakstītu ziņu uz jauno numuru.","3G9LoK":"{name} veica tālruņa numura maiņu. Šobrīd saziņa notiek ar šīs personas jauno tālruņa numuru.","1AZhZI":"Skatīt kanālu","4rl2hN":"Pievienot grupai","2Bf154":"Jūs vairs neesat grupas administrators. Tikai grupas administratori var pievienot grupai dalībniekus.",ndTlo:"Lietotājs {requester-name} jau ir pievienots grupai “{subgroup-name}”.","43A7tc":"Aptaujās nav pieejamas darbības ar tastatūru.","21ygbG":"{poll-name} Lielāko balsojumu skaits: {poll-results}. {no-kb-navigation}","3yQBSQ":"{text-content}… Lasīt vairāk","3KpkUp":"atbilde",KLyBv:"attēls","22LuTu":"GIF attēls","29mv0c":"Video","43zKOL":"{reply-message} {msg-type}",wx6Ny:"ir reakcija","4crV8a":"ir reakcijas","3dWV40":"{author-name} {message-type} {message-text} {time-sent} {message-status} {message-edited} {has-reaction}",THELh:"Vai lejupielādēt vizītkarti?","3Ii3sv":"Šo kontaktinformāciju nav iespējams parādīt WhatsApp Web. Vai lejupielādēt to, lai atvērtu datorā, izmantojot citu lietojumprogrammu?",mZ5DE:"Rakstīt ziņu","4BEiPi":"Skatīt uzņēmumu",KSGHC:"Ziņa nosūtīta, izmantojot reklāmu","4eZcUM":"Atvērta",hKfZf:"Beidzies fotoattēla derīguma termiņš","3GxyJh":"Beidzies video derīguma termiņš","3HKvpZ":"Beidzies derīguma termiņš","2obYts":"Vienreiz skatāms fotoattēls","3nw2h1":"Vienreiz skatāms video","1ChROe":"Ziņu noklusētā taimera iestatīšana no tīmekļa/darbvirsmas vēl nav pieejamu. Varat iestatīt taimeri mobilajā ierīcē, pārejot uz sadaļu Iestatījumi > Privātums > Izgaistošās ziņas.","1oCLyG":"{subgroup-name}","3GbuOB":"Nosūtīt pievienošanās pieprasījumu",oLIhV:"Jūs pieprasījāt lietotājam {person-name} kopīgot tālruņa numuru.","4zqOt6":"{person-name} pieprasīja jums kopīgot tālruņa numuru.","4shIOZ":"Kopīgot tālruņa numuru",mDvEI:"Lai atjauninātu, klikšķiniet šeit.","2LoGjd":"Jūs saņēmāt vienreiz skatāmu ziņu. Papildu privātumam varat to atvērt tikai tālrunī. {=m2}",YpcU3:"Uzzināt vairāk","2W8swU":"Atbildēt","23aeBt":"Jūs:","4BX1T3":"{author}:","2ZKFVj":"Šī ir izgaistošā ziņa","3oWx14":"Atspraust","23jUX3":"Piespraust","34umJU":"Atcelt paturēšanu","1HcH0g":"Paturēt","4ouSa6":"Nosūtīt administratoram pārskatīšanai","4fwh3d":"Rediģēt ziņu","17dZou":"Ziņot",QwE3j:"Šajā sarakstē ir ieslēgtas izgaistošās ziņās. Šis pasūtījums neizgaisīs, jo tajā ietverta informācija par pirkumu.","375ot9":"Uzzināt vairāk",yf7J1:"Informācija","21wSIT":"Informācija",AxUeS:"Pārsūtīt","1BRalt":"Meklēt tīmeklī","124PTy":"Informācija","2D0P7f":"{name} veica tālruņa numura maiņu.","4pjpb0":"Rakstīt uz jauno numuru","3Cs2oY":"Grupas apraksts","4uawDA":{4:"{=m0} {pendingRequests} pievienošanās pieprasījums","*":"{=m0} {pendingRequests} pievienošanās pieprasījumi",_1:"{=m0} 1 pievienošanās pieprasījums"},oBL7I:{"*":"Pārskatiet:",_1:"Review"},Gf872:"Lai skatītu un nosūtītu reakcijas šajā sarakstē, {=m1}","4abMhF":"atjauniniet WhatsApp","2s2ugo":"Šis uzņēmums nevar sarakstīties ar lietotājiem, kuru tālruņa numuriem ir jūsu valsts kods.",sGN9n:"Šis uzņēmums nevar sarakstīties ar jūsu valsts koda tālruņa numuriem.","3zXAL2":"Vairs neesat dalībnieks.","2bXahd":"Jūs bloķējāt WhatsApp","2gtc1t":"Tikai uzņēmuma WhatsApp pārstāvji var sūtīt ziņas",dG42O:"Jūs nobloķējāt WhatsApp Surveys","2sN1Zb":"Varat bloķēt šo saraksti, lai atteiktos no ziņu saņemšanas no WhatsApp Surveys",mRFf6:"Nevarēja attēlot ziņas informāciju. Vēlāk mēģiniet vēlreiz.","1n00VC":"Fona attēla priekšskatījums",Qfqyc:"Šajā grupā ir vairāk par 256 dalībniekiem, un tai ir automātiski izslēgta skaņa, lai samazinātu paziņojumu skaitu.","1GqSyx":"Ieslēgt skaņu","1q77mG":"Labi",LUJtH:"Kontaktinformācija","2SQkZ4":"Ziņot","1JlKyP":"Atbloķēt","2EwszA":"Bloķēt",a77h0:"Sarakstes piešķiršana","2Yz01q":"Izgaistošās ziņas","33Son5":"Lai saņemtu vecākas ziņas no tālruņa, noklikšķiniet šeit.",D8p0m:"Lai skatītu vecākas ziņas, izmantojiet lietotni WhatsApp tālrunī","2iINqQ":"Izmantojiet lietotni WhatsApp tālrunī, lai skatītu ziņas pirms: {date}.","2SfTUZ":"Grupas info","29k6qZ":"Izgaistošās ziņas","1cXRzw":"Apraides saraksta informācija","3UK0Wq":"Ritināt līdz apakšai","18ssTb":"Ziņot","20I6qR":"Jūs pievienoja kāds, kurš nav jūsu kontaktu sarakstā","1piCbA":"Varat pārvaldīt, kuri lietotāji var pievienot jūs grupām, {=m2}",sfNLn:"privātuma iestatījumos","3KaKZa":"Pamest kopienu","1GqETb":"Pamest grupu","4CoaPf":"Sūtītājs nav kontaktu sarakstā","4B5PLS":"Šis oficiālais uzņēmuma konts nav jūsu kontaktu sarakstā.","3VQ61G":"Šis uzņēmuma konts nav jūsu kontaktu sarakstā.",UBgxv:"Safety tools","42ybx":"Atvērt informāciju par saraksti ar lietotāju {author-name}","3nV0kV":"Uzaicinājuma saite nav pieejama","4iPF7L":"Nevarat pievienoties šai grupai, jo uzaicinājuma saite nav pieejama.","2GPPmB":"Nevarat skatīt šīs grupas uzaicinājuma saiti, jo neesat administrators.","3qjGKv":"Uzaicinājums, izmantojot saiti, nav pieejams","4wgNSd":"Uzaicinājums, izmantojot saiti, nebija pieejams",wdAra:"Uzaicinājums, izmantojot saiti, šai grupai īslaicīgi nav pieejams.",txY0N:"Uzaicinājums, izmantojot saiti, šai grupai īslaicīgi nebija pieejams.","3lsTqx":"Noraidīt","2bUy2n":"Uzzināt vairāk","2KpRiy":"Šī kopiena vairs nav pieejama.","2JIdcX":"Uzzināt vairāk","3XmS1