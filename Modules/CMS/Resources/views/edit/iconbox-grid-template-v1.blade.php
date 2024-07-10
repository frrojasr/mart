@php
    $component = isset($component) ? $component : null;
@endphp
<div class="card dd-content {{ $editorClosed ?? 'card-hide' }}">
    <div class="card-body">
        <form action="{{ route('builder.update', ['id' => '__id']) }}" data-type="component" method="post"
            class="component_form form-parent silent-form" novalidate>
            @csrf
            @include('cms::hidden_fields')
            <div class="form-group row">
                <label class="col-md-3 control-label">{{ __('Iconbox Grids') }}</label>
                <div class="col-md-8">
                    <div class="accordion iconbox-accordion {{ $accordId = uniqid('accord_') }}" id="accordionExample">
                        @php
                            $iconboxes = $component && is_array($component->iconbox) ? $component->iconbox : [];
                            $totalIconBoxes = count($iconboxes);
                        @endphp
                        @forelse ($iconboxes as $iconbox)
                            @php
                                $iconbox = miniCollection($iconbox);
                            @endphp
                            <div class="card cta-card mb-3">
                                <div class="card-header p-2" id="headingOne">
                                    <div class="mb-0 ac-switch collapsed d-flex closed justify-content-between align-items-center w-full curson-pointer"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $ac = 'ac' . randomString() }}"
                                        aria-expanded="true" aria-controls="{{ $ac }}">
                                        <div>{{ __('Icon Box') }}</div>
                                        <span class="b-icon">
                                            <i class="feather icon-chevron-down collapse-status"></i>
                                            <span class="accordion-action-group">
                                                @if ($loop->last)
                                                    @if ($totalIconBoxes > 1)
                                                        <span class="accordion-row-action remove-row-btn"
                                                            data-parent="{{ $accordId }}"
                                                            data-index="{{ $loop->index + 1 }}"><i
                                                                class="feather icon-minus"></i></span>
                                                    @endif
                                                    <span class="accordion-row-action add-row-btn"
                                                        data-parent="{{ $accordId }}"
                                                        data-index="{{ $loop->index + 1 }}"><i
                                                            class="feather icon-plus"></i></span>
                                                @else
                                                    <span class="accordion-row-action remove-row-btn"
                                                        data-index="{{ $loop->index + 1 }}"
                                                        data-parent="{{ $accordId }}"><i
                                                            class="feather icon-minus"></i></span>
                                                @endif
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div id="{{ $ac }}" class="card-body collapse parent-class"
                                    aria-labelledby="headingOne" data-parent=".{{ $accordId }}">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="preview-image">
                                                        @if ($iconbox['image'])
                                                            <div class="d-flex flex-wrap mt-2">
                                                                <div
                                                                    class="position-relative border boder-1 media-box p-1 mr-2 rounded mt-2">
                                                                    <div
                                                                        class="position-absolute rounded-circle text-center img-remove-icon">
                                                                        <i class="fa fa-times"></i>
                                                                    </div>
                                                                    <img class="upl-img" class="p-1"
                                                                        src="{{ asset('public/uploads') . DIRECTORY_SEPARATOR . $iconbox['image'] }}"
                                                                        alt="{{ __('Image') }}">
                                                                    <input type="hidden"
                                                                        name="iconbox[{{ $loop->index }}][image]"
                                                                        id="validatedCustomFile"
                                                                        value="{{ $iconbox['image'] }}">
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>

                                                <label class="col-sm-12 control-label">{{ __('Icon') }}</label>

                                                @php
                                                    $rand = uniqid();
                                                @endphp
                                                <div class="col-md-12">
                                                    <div class="custom-file media-manager"
                                                        data-name="iconbox[{{ $loop->index }}][image]"
                                                        data-val="single" id="image-status">
                                                        <input class="custom-file-input form-control d-none"
                                                            id="validatedCustomFile{{ $rand }}" maxlength="50" accept="image/*">
                                                        <label class="custom-file-label overflow_hidden position-relative d-flex align-items-center"
                                                            for="validatedCustomFile{{ $rand }}">{{ __('Upload image') }}</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 control-label">{{ __('Title') }}</label>
                                                <div class="col-sm-12">
                                                    <input type="text" class="form-control inputFieldDesign"
                                                        value="{!! $iconbox['title'] !!}"
                                                        name="iconbox[{{ $loop->index }}][title]">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group row">
                                                <label class="col-sm-12 control-label">{{ __('Subtitle') }}</label>
                                                <div class="col-sm-12">
                                                    <input type="text" class="form-control inputFieldDesign"
                                                        value="{!! $iconbox['subtitle'] !!}"
                                                        name="iconbox[{{ $loop->index }}][subtitle]">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="card cta-card mb-3">
                                <div class="card-header p-2" id="headingOne">
                                    <div class="mb-0 ac-switch collapsed d-flex closed justify-content-between align-items-center w-full curson-pointer"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $ac = 'ac' . randomString() }}"
                                        aria-expanded="true" aria-controls="{{ $ac }}">
                                        <div>{{ __('Icon Box') }}</div>
                                        <span class="b-icon">
                                            <i class="feather icon-chevron-down collapse-status"></i>
                                            <span class="accordion-action-group">
                                                <span class="accordion-row-action add-row-btn"
                                                    data-parent="{{ $accordId }}" data-index="1"><i
                                                        class="feather icon-plus"></i></span>
                                            </span>
                                        </span>
                                    </div>
                                </div>
                                <div id="{{ $ac }}" class="card-body collapse parent-class"
                                    aria-labelledby="headingOne" data-parent=".{{ $accordId }}">
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <div class="form-group row">
                                                <div class="col-md-12">
                                                    <div class="preview-image">
                                                    </div>
                                                </div>
                                                <label class="col-sm-12 control-label">{{ __('Icon') }}</label>

                                                @php
                                                    $rand = uniqid();
                                                @endphp
                                                <div class="col-md-12">
                                                    <div class="custom-file media-manager"
                                                        data-name="iconbox[0][image]" data-val="single"
                                                        id="image-status">
                                                        <input class="custom-file-input form-control d-none"
                                                            id="validatedCustomFile{{ $rand }}" maxlength="50" accept="image/*">
                                                        <label class="custom-file-label overflow_hidden position-relative d-flex align-items-center"
                                                            for="validatedCustomFile{{ $rand }}">{{ __‹}øCÁ;Eèƒ  …ÉtAÿˆC÷ÙŠ9ˆCƒÁuõ‰M‹MÜC‹}ØƒêÁé‰Uà‰EØƒúsŠÂÀàÁˆ‹]Øë€é ˆ‹ØBùˆC‹EÜˆC‰]ü‹]à‹Uô¶GÁâÐ‹ÊkÂÁé3Ê‰UôÁéÈáÿ  QMðèW  ‰8G‹ÃK…ÀuÉ‹]ü‹M‰}øë6‹MAG‰M‰}øƒù u&C!;EèsPwàÆj{ƒÃ!Yó¥‹}ø3ö‹Î‰]ü‰M‹Uô;}ì‚`þÿÿ…ÉtCÁ;EèsAÿˆC÷ÙŠ9ˆCƒÁuõ‹ó+uMðèÔýÿ_‹Æ^[ÉÃU‹ìQQSVW‹ù‹M‹ñM‰M‰Eø¶Gƒû s$C3‰Eü;Á‡†   SWVè°½0 ‹uüƒÄû‹]ëN‹Ó‹ÎƒãÁêÁã+ËIƒúu¶ƒÂG¶+ÈFÂG;EwE‹];Ër1ŠˆŠAˆFƒÆƒÁŠˆFAƒêuõ‹M;ñs	;}ø‚uÿÿÿ+ó‹ÆëèÇ0 Ç    ëèøÆ0 Ç    3À_^[ÉÃU‹ì‹	‹E…Ét‹Që3Ò…Àx;Â}ƒÀë¸ˆVM]Â U‹ìQS‹Ù3ÒVW‹…Àt‹xë‹ú¹ €  ;ù„ž   ƒ‘   …Àt‹P;Ñsp…ÒtkòÑîë‹ñ;ñjBñµ   PèÂ0 ‰EüYY…Àtc3É‰pA‡Hº €  èƒÁþÿ‹uü¹ €  ‰N‹…ÀtÿuüƒÀ‹ÑWPNè„ÕÿÿƒÄ‹Ëè4ýÿ‰3ë‹ÑH+×¹èCÁþÿ‹¹ €  …Àt‰H_^[ÉÂ h  ¸àª6è‰ž2 ‹ñ‹E3ÛQ‹ÌCPˆ^èAXþÿäýÿÿè ’ÿÿY3ÿ3À‰}ü‹•äýÿÿŠNƒÂƒNÿƒNÿ„ÉW•À‰~h€   ƒÀ„ÉPWjXEÃ»   €P¸   ÀEÃPRÿÒ9‰Fƒøÿ„÷   ŠÐ¥J„ÛuèÅ  ŠÐ¥J¡È¥J‰…àýÿÿ…ÀtNh  …èýÿÿWPèž0 ƒÄ„Ûuè’  ¡È¥Jë‹…àýÿÿWh  èýÿÿQÿvÿÐ…ðýÿÿPNè'Xþÿë…äýÿÿPNèhXþÿWÿvÿÒ9ŠN3ÒW‰F„É¸   EÂPWjXj[EÃPWÿvÿÒ9ƒ~ÿ‰Ft…Àu‹ÎèM   ë-…Àt€~ WWWjYEÙSPÿ Ò9‰F‹~…ÿu
‹Îè!   ‹~ÇEü   2Ò‹äýÿÿèãþüÿ‹Çè©œ2 Â SV‹ñ3Û9^t;9^tG9^t18^u 9^tÿvÿvÿ$Ò9…Àuh E>èÿ.ÿÿYÿvÿ(Ò9‰^9^tÿvÿ,Ò9‰^‹F…Àt3ƒøÿt.‹È8^u9^tSSÿvPÿ0Ò9ÿvÿ4Ò9‹NQÿ,Ò9‰^^[ÃU‹ìV‹ñƒf ƒf öEÇHE>t
jVè€0 YY‹Æ^]Â h  ¸§6è.œ2 €=Ð¥J tƒ=È¥J •Àé  ÿ5À¥JäýÿÿÆÐ¥JèÊUþÿ…äýÿÿ3ÛhÌ¥JP‰]üèžªÿÿ‹Èè‰Îÿÿ„À…‰   3ÀSf‰…èýÿÿ…èýÿÿj%PSÿÔ9…ÀuÇEü   éž   hÜ=>¾  …èýÿÿVPè¸l1 ÿ5À¥J…èýÿÿVPè¥l1 ƒÄ…èýÿÿjSPÿøÑ9£Ì¥JèªÿÿjäýÿÿQ‹Èè"Îÿÿ‹Ì¥J‰¡Ì¥J…Àt%ÿ5Ä¥JPÿüÑ9£È¥J…Àu‹Ã‰Ì¥J£È¥Jë¡È¥J…À•ÃÇEü   ‹äýÿÿ2ÒèáüüÿŠÃè§š2 ÃùÏTtùUt2ÀÃ°ÃV‹2èâÿÿÿ„Àt¾3ÉAë·jYÎ‰
^Ãj0¸«6è†š2 ‰Uä‹Ù3ÉU‰MÜƒÏÿ‰Mà‰Mì‰Mè‹Ëè´ÿÿÿ‹u‹UàÇEÐ   ‹È‹Â…É‰uÄ‰uÈDEÐ‰EØ‰uÌƒè „Ý  ƒè„‡  ƒè„D  ƒè„.  ƒè„Õ  ƒè„€  ƒè…Ð  ƒùf  „ç  ƒùS…  „î   ƒé%„Þ   ƒét&IƒétIƒé…  ‹EìƒÈ éÁ  ‹EìƒÈ éj  ‹ËèãþÿÿƒÆ„À„‹   ‹Fü‰uƒø}Péb  f‰EÔ3Àf‰EÖèúüÿQQ‰EØMÔ‹Ä‰MØÇ@   èÔ¢ÿÿ‹u3À‰Eü‹øèåùüÿ‹MØ;Èt";ysWMØèdZÿÿ¾ PVEèPÿuäÿÓƒÄGëÒÇEü   è„üüÿƒMüÿ‹uéi  ŠFü<s¶Àéqÿÿÿj?éÒ   j%éË   ‹Ëè)þÿÿƒÆ„À‹FütAƒÿÿu3É‹ùf9tGf9xuù‹MÜ…É~	…ÿx;ÏOù‹UäWQÿuì‹ËPÿuEèPèž  é÷  ƒÿÿu3É‹ù8tG88uú‹MÜ…É~	…ÿx;ÏOù‹UäWQÿuì‹ËPÿuEèPèW  é¸  ƒéXtIƒét"ƒé„  ƒé…Ÿ  ‹Eì‹MÌòqPë:‹Ëèlýÿÿÿ6ƒÆ„ÀÿuEèPÿuäÿÓƒÄém  ‹EìƒÈ éQ  ‹EÈÿuìò p‹UäEèWÿuÜQQò$‹ËÿuPè3
  ƒÄé1  ƒùpö   „ß   ƒég„Æ   Iƒét}ƒétAƒé…  ƒMì@ƒúu·ƒÆëƒútƒúu
‹ƒÆ‹Nüë‹ƒÆ3ÉÿuìWÿuÜjQë`‹ƒÆƒúuf‹Eèf‰é·  ‹Eè‰ƒú„©  ƒú…   3À‰Aé–  ƒútƒútƒúu
‹ƒÆ‹Vüë‹ƒÆ™ÿuìWÿuÜj
R‹Uä‹ËPÿuEèPèì  ƒÄ éS  ‹Eì‹MÄ€   éªþÿÿÿuì‹ƒÆWÿuÜ™jëÄƒés„   Iƒét]IƒétHƒé…  ‹EìƒÈ@‰Eìƒúu·ƒÆëƒútƒúu
‹ƒÆ‹Nüë‹ƒÆ3ÉÿuìWÿuÜjéÿÿÿU‹ËèÅûÿÿéÆ   ƒMì@ƒúu·ƒÆëƒútƒúu
‹ƒÆ‹Nüë‹ƒÆ3ÉÿuìWÿuÜj
é¼þÿÿ‹ËèiûÿÿƒÆ„À‹Füt<ƒÿÿu3É‹ù8tG88uú‹MÜ…É~	…ÿx;ÏOù‹UäWQÿuì‹ËPÿuEèPè%  ë<ƒÿÿu3É‹ùf9tGf9xuù‹MÜ…É~	…ÿx;ÏOù‹UäWQÿuì‹ËPÿuEèPèÖ  ƒÄU‹Ëèðúÿÿ3Ò3ÿ‰}ìƒÏÿ‰UÜ‰Uàé4ûÿÿƒùLt*ƒùht ƒùlu2jXU‰Eà‹Ëè¼úÿÿ‹Èƒùlujë3À@ëjXU‰Eà‹Ëèœúÿÿ‹Èj‹UàXéìúÿÿŠÁ,0<	w&3À…ÿIÇyÐkÀ
øU‹Ëènúÿÿ‹Uà‹È‹EØé¾úÿÿƒù*u‹>UƒÆ‹ËèMúÿÿ‹Èjë¯jXéœúÿÿƒù.uójX‰EØëºŠÁ,0<	wkEÜ
ƒÀÐÁ‰EÜë¤ƒù*u‹UƒÆ‰EÜ‹Ëèúÿÿ‹Èjédÿÿÿjë³‹Áƒè t=ƒèt/ƒèt!Hƒètƒètjë“ƒMìéWÿÿÿƒMìéNÿÿÿƒMìéEÿÿÿƒMìé<ÿÿÿƒMìé3ÿÿÿƒù%u3À@‰EØëQÿuEèPÿuäÿÓƒÄU‹Ëè„ùÿÿ‹È‹EØjZ;Â‹Uà…Îùÿÿ‹U…ÒtK‹ËèMùÿÿ‹Mè„ÀBÿt;Ès
‹Eä3Òˆë,…Òt(‹Eä3ÉˆLÿë;Ès‹Eä3Òf‰Hë…Òt
‹Eä3Éf‰LPþ‹Eèè’“2 ÃU‹ìƒìSV3ö‰Uü9u»LE>‹Ö‰MøE]W‰]ô8tB€< uù‹E+Âj YIÈ‹E‹ù÷ß$Dù‰}…ÿ~#‹]ü;u}j ÿuÿuSÿUøƒÄOF…ÿæ‹]ô‰}€; t#‹}ü;u}¾PÿuÿuWÿUøƒÄCF€; uã‹}…ÿy;u}j ÿuÿuÿuüÿUøƒÄFƒÇxä_^[ÉÃj¸;«6è“2 ‰Uì‰Mè3öMä9u»TE>‹þE]S‰]ðè8þÿMä‰uüè9Jþÿ‹MV+È‹EZIÑ‹ò÷Þ$Dò‰u…ö~#‹]ì;}}j ÿuÿuSÿUèƒÄNG…öæ‹]ð‰u3Àf9t‹uì;}}t‰EMf‹ƒÃf‰EEP‰]ðè7þÿƒeà MÆEüè¾Iþÿ…À~.‹]àSMèlSÿÿ¾ PÿuÿuVÿUèƒÄMGCè“Iþÿ;Ø|Ø‹]ðÆEü‹Mè~õüÿ3ÀˆEüf9u‡‹u…öy;}}j ÿuÿuÿuìÿUèƒÄGƒÆxäÇEü   ‹MäèBõüÿè£‘2 ÃU‹ìƒì3À‰Uü9E‹ÐS»TE>‰MøE]VW‹ø‰]ôf9tBf9Suù‹E+Âj YIÈ‹E‹ñ÷Þ$Dñ‰u…ö~#‹]ü;}}j ÿuÿuSÿUøƒÄNG…öæ‹]ô‰u3Àf9t'‹uü;}}·PÿuÿuVÿUøƒÃƒÄ3ÀGf9uß‹u…öy;}}j ÿuÿuÿuüÿUøƒÄGƒÆxä_^[ÉÃj¸^«6è	‘2 ‰Uì‰Mð3ö»LE>9uE]‹ûOŠG„Àuù+ùè$ñüÿQQ‰EM‹Ä‰‰xèeùüÿ‹E‹]ì+Çj YIÈ‰uü‹E‹ù÷ß$Dù‰}…ÿ~;u}j ÿuÿuSÿUðƒÄOF…ÿæ‰}MèLþÿ;ð}1‹}ð;u}&VMè~Rÿÿ· PÿuÿuSÿ×ƒÄMFèíKþÿ;ð|Õ‹}…ÿy;u}j ÿuÿuSÿUðƒÄFƒÇxæÇEü   2Ò‹Mè2òüÿèå2 ÃU‹ìƒì8¡€J3Å‰Eü‹ES‹]V‰EØ3ö3À‰MÜ3É‰UÔ9E ‹UME W‰EÐ‹E$‰uà‰Mäj _¨@u&…Ò|…Ûs÷Ûj-Ñ^÷Úë¨tj+^ë¨E÷‰uà¨ ¾dE>¸„4>DÆ‹ðSj ÿu‹ùRSèv2 ‰]Ì[Š1‹Ø‹EäˆLè‹ÈA‹ÃÂ‰Mätƒù|Ð‹uàƒÿEù‰}Ìƒÿƒ  ‹UÐ‹Ç;×ÆD=è ‹ÊMÂ‹U+Ð3À…ö•À3Û+ÏIÙ‹M$3ö‰]Ð+ÐIò‹Ñƒât;Þ‹ÆMÃ‹Ø‰EÐ3À…ÒDÆ‹ð÷Þ€áDð‰uä…ö~"‹}Ü‹]Ôj ÿuÿuØSÿ×ƒÄN…öí‹}Ì‹]Ð‰uä‹Eà…ÀtPÿuÿuØÿuÔÿUÜƒÄ…Û~‹}Ü‹uÔj0ÿuÿuØVÿ×ƒÄK…Ûí‹uä‹}Ì‹]Ü…ÿ~‹uÔ¾D=çOPÿuÿuØVÿÓƒÄ…ÿé‹uä…öy÷Þj ÿuÿuØÿuÔÿÓƒÄƒîuë‹Mü_^3Í[è9q0 ÉÃèùz0 ÌU‹ìò-¿Bƒì(á(Ý(Ô3ÒV‹ñò,Â(ÂòXÅfnÈóæÉf/Ár(Âò\Åf/ÈsòYà½BBòY€ÀBƒúd|Äƒúdu	WÀòë3…Òt$òYËMðò\áòMø(Ìè~ÿÿÿòMøòXMðëò\Ñ(Âò^ÉÃU‹ìƒäøìÀ  ¡€J3Ä‰„$¼  ‹EòM‰D$3ÀV‰D$‰D$(D$<W‹ù‰T$P‰|$òL$Dèˆ`1 YfƒøuB‹t$°#ÇD$ xE>¾ÀPÿuÿt$Vÿ×‹D$0ƒÄ@‰D$ Š „Àuà‹Œ$Ä  _^3Ìèp0 ‹å]Ãƒ} Wÿòuj_M}f/þj YvW5ÐÊBÇD$(-   ëöE t
ÇD$(+   ëöE j XEÁ‰D$(jX;øL$0(ÎOø‰|$<èlþÿÿò¿B‹Ç…ÿtòY€ÀBƒèuóòD$0ò\ðòD$HòYñ(Æòuè©2 ‹Ê‰D$ ‰L$‹ÈèSª2 òM‹L$ ò\È‹T$f/P¾BrƒÁƒÒ è-ª2 (ðò¿Bòt$@‹Çòt$ (È…ÿtòY€ÀBƒèuóf/ñò|$Hr-òXø‹Çò|$0…ÿtòY€ÀBƒèuóò\ðòt$@òt$ ‹t$WöòY=à½BL$0‰t$(Ïètýÿÿ(Ç¹dE>ò|$0ò\ÇòX¨½BòY(ÄBò,À+ÈŠˆ„4ˆ  Ff.þŸöÄD{þ7  |¤‹D$¹6  òt$@;ÁEÆ‹t$,‰D$=7  ƒÖ  WÿÆ„ˆ   f.÷ŸöÄD‹Ž   òY5à½BL$ (ÎèÜüÿÿ(Æ¹dE>òt$ ò\ÆòX¨½BòY(ÄBò,À+ÈŠˆD4PFf.÷ŸöÄD{þ7  |«‹Î;Ï}‹Ç+ÁPD$TÆj0Pè6‹0 ƒÄ‹Ï¸7  ‹ñ;ÈHÿDñ‰t$,;ðƒ/  ‹M3À9D$(j •ÀÆD4T +È‹Ç+L$ +Ï+ÆZIÐ‹E ‰T$ 3ÒƒéIÑ‹Ê÷Ù$DÊöE ‹ù‰L$0‰|$tX‰|$…É~P‹D$(‰|$…ÀtPÿuÿt$ÿt$ ÿT$ƒÄOƒd$( ‰|$…ÿ~J‹t$j0ÿuÿt$ÿt$ ÿÖƒÄO…ÿé‹t$,‰|$…ÿ~#‹t$j ÿuÿt$ÿt$ ÿÖƒÄO…ÿé‹t$,‰|$‹D$(…ÀtPÿuÿt$ÿt$ ÿT$ƒÄ‹D$…À~3‹|$‹t$H‰D$¾„ˆ  Pÿuÿt$Vÿ×‹D$,ƒÄ…ÀÝ‹t$,‹|$öE €„   L$P‹Ö‰L$ …ö~3À€|P0uAN@;Â|ò‰L$ ƒ|$< ~<…ö~8‹|$j.ÿuÿt$ÿt$ ÿ×ƒÄ‹D$ N¾0Pÿuÿt$ÿt$ ÿ×ƒÄ…öâ‹|$‹t$…ÿ‰äûÿÿ÷ßj ÿuÿt$ÿt$ ÿÖƒÄƒïuééÆûÿÿƒ|$< ~;j.ÿuÿt$ÿt$ ÿT$ƒÄ…ö~#‹|$¾D4ONPÿuÿt$ÿt$ ÿ×ƒÄ…öå‹|$ƒ|$  ~‹t$‹|$j0ÿuÿt$WÿÖ‹D$0ƒÄH‰D$ …Àä‹|$édÿÿÿè&u0 ÌU‹ìV‹u‹;Us‹EŠMˆ‹B‰^]ÃU‹ìV‹u‹;Us‹Ef‹Mf‰P‹B‰^]ÃU‹ì‹M¾EP‹ÿ„Àt‹Eÿ ]ÃU‹ì‹M·EP‹ÿ„Àt‹Eÿ ]ÃU‹ìÿuÿuR‹Ñ¹ÏTè[íÿÿƒÄ]ÃU‹ìÿuÿuR‹Ñ¹ðTè@íÿÿƒÄ]ÃU‹ìQ‹UE‹MPÿuè´ÿÿÿYYY]ÃU‹ìQ‹UE‹MPÿuè´ÿÿÿYYY]ÃU‹ìƒì‹U3ÀSVW‹ù‰Eø3É3ö3Û‰Mô…ÒŽD  ;Ês‹EÁë¸ÂòL¶ ðCƒû…™   ‹ÆMüÁø3ÛCSQŠ€À;‹ÏˆEü‹ÿ‹ÆMüÁøƒà?SQ‹ÏŠ€À;ˆEü‹ÿ‹ÆMüÁøƒà?SQ‹ÏŠ€À;ˆEü‹ÿƒæ?MüSQ‹ÏŠ†À;ˆEü‹ÿ‹EøƒÀ‰EøƒøP|‹MüSQ‹ÏÆEü
ÿ3À‰Eø‹U3ö‹Mô3ÛëÁæA‰Mô;ÊŒ;ÿÿÿ…ÛtyjY+ËÁáÓæMü‹ÆÁøjQ‹ÏŠ€À;ˆEü‹ÿ‹ÆMüÁøƒà?jQ‹ÏŠ€À;ˆEü‹ÿ‹‹Ïj‹ƒûuÆEü=ëÁþƒæ?Š†À;ˆEüEüPÿÒ‹MüjQ‹ÏÆEü=ÿ_^[ÉÃU‹ìƒìS3Û‰MðVW8¬¤Jt^h   jÿhMèi†0 ƒÄjAYA¿ˆMAƒùZvñjaYA¹ˆMAƒùzvñj0YAˆMAƒù9vñÆ;M>Æ?M?ˆ¬¤J‹u‹û‹Ö…Òt(‹];þ;r¸DóL¶ Jÿ€¸M MÊG‹Ñ;þrß3Û‹Â‹ûÁèƒâkÀƒúHEÈƒú‹Ó‰UøAEÁ‰Eì‹Ã‰Eô…öt\‹M;ÆsÁë¸DóL¶ ¾€M…Àx3ÁçƒÃøƒû|&ƒëUü‹Ë‹ÇÓø‹MðˆEüjR‹ÿ‹Uø‹uB‹M‰Uø‹Eô@‰Eô;Ær«;Uì_^”À[ÉÃU‹ìƒìS3Û‰MðVW8¬¤Jt^h   jÿhMè+…0 ƒÄjAYA¿ˆMAƒùZvñjaYA¹ˆMAƒùzvñj0YAˆMAƒù9vñÆ;M>Æ?M?ˆ¬¤J‹u‹û‹Ö…Òt)‹];þJÿ¸´òLBÃ¶ €¸M MÊGƒÃ‹Ñ;þrÜ3Û‹Â‹ûÁèƒâkÀƒúHEÈƒú‹Ó‰UøAEÁ‰Eì‹Ã‰Eô…öt[‹M;ÆAr¸´òL¶ ¾€M…Àx3ÁçƒÃøƒû|&ƒëUü‹Ë‹ÇÓø‹MðˆEüjR‹ÿ‹Uø‹uB‹M‰Uø‹Eô@‰Eô;Ærª;Uì_^”À[ÉÃj¸«6è¢ƒ2 ‹ñ~‹…Àu_èÔãüÿ‰Eðƒeü Mì‹QMèQMäQMðQ‹ÎÿPDfnEä‹jÿuì[ÀÿuèQMðó$Q‹ÎÿP,P‹Ïè±-þÿÇEü   2Ò‹MðèKåüÿ‹èü‚2 ÃU‹ì‹U‹MèL6' ‹E]Â j¸¬6èƒ2 ‹ù‰}ð3öÇü=‰w‰uüÇE>‰w‰w_‰]ìÇˆE>‰sj XÆEüh„   ‰C‰Eèèßf0 Y‰EäÆEü…Àtj YhÔ> hÀ}Qp‰jVèwj0 ‰s3ö‰w j\ÆEüèÑf0 Y‰EäVVQQ‹ÔÆEü‹È‰2‰rè3ƒ  POÆEüèÑ,þÿ‹Çè-‚2 ÃU‹ìV‹ñè   öEt
j$VèOf0 YY‹Æ^]Â U‹ìjÿh,¬6d¡    PVW¡€J3ÅPEôd£    ‹ñ3ÿ‰}ü‹N …Ét‹ÿP‰~ Nè²%  ÇEü   ‹N…Ét‹ÿP‰~ÇEü   ‹N…Ét‹ÿP‰~‹FÇü=‹Môd‰    Y_^ÉÃVW‹y‹wFèë‹‹ÎÿPX‹F‹ðƒÀè÷Þö#ð;÷uç_^ÃVW‹ù‹w0ë3‹N‹Ö‹A;Æu‹F‰AëH‹;Æt…Àuóë‹F‰‹vQ‹Êè
   …öuÉ!w0_^ÃU‹ìjÿhTZ6d¡    PV¡€J3ÅPEôd£    ‹ñƒeü ‹N…Ét
‹QÿPƒf jVèe0 YY‹Æ‹Môd‰    Y^ÉÂ U‹ìƒì(‹S‹ZV‹rW‹z‹‰Uä‹Q+Uä‰MôB‰}ø‹y‹I+Ï‰EüÃ‰}à‹}øA‰]ð‰uì;Ð}%‹Eü…Àu‹Ú‰Uðë…Ût‹Ú+Ø‰]ð…Ûy3Û‰]ð‰Uü>;È}"…ÿu‹ñ‰Mìë…öt‹ñ+÷‰uì…öy3ö‰uì‰Mø‹Eü+Î‹u+Ó‰Uè‹Ú‹Uä+Ø‹ù‰MØ‰J‹MøÂ+ù‰]Ü‹]àI‰^‰F‹ÃÁ‹]Ü‰F‹Eô‹Mô‹UðJ‹ Eü‹I‰FH‰NÃ‰F‹EøHÁ‹Mô‰F‹Eè‹I‰F Â‹Uø‰N$‰F(BÿÁ‰F,‹Eô‹‹@Ð‹Eü‰V4H‰N0Á‰F8BÿÇSÿ‹]‰F<‹E