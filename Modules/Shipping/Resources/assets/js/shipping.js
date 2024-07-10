'use strict';

$('.conditional').ifs();
$("#v-pills-general-tab").trigger('click');

$(document).on("click", '.tab-name', function () {
    var id = $(this).attr('data-id');
    $('#theme-title').html(id);
    $('.tab-pane[aria-labelledby="home-tab"').addClass('show active')
    $('#' + $(this).attr('id')).addClass('active').attr('aria-selected', true)
});

$(document).on('keyup', '.float-validation', function() {
    var number = $(this).val();
    if (number == '') {
        return;
    }

    var split = number.split('.');
    var first = split[0] ?? 0;
    var second = split[1] ?? 0;
    if (split[0].length > 8) {
        first = split[0].substring(0, 8);
    }
    if (split.length > 1 && split[1].length > 8) {
        second = split[1].substring(0, 8);
    }

    if (split.length > 1) {
        $(this).val(first + '.' + second);
    } else {
        $(this).val(first);
    }
})

// Change switch with value
$(document).on('click', '.cr', function() {
    var value = $(this).closest('.switch').find('input').val();
    if (value == 1) {
        $(this).closest('.switch').find('input').val(0)
    } else {
        $(this).closest('.switch').find('input').val(1)
    }
})

$(document).on('click', 'label.free_shipping_checkbox', function() {
    if ($(this).siblings('input').attr('checked') == 'checked') {
        $(this).siblings('input').val(0).attr('checked', false)
    } else {
        $(this).siblings('input').val(1).attr('checked', 'checked');
    }
})
$('select[name="free_shipping_requirement"]').each((k, v) => {
    freeShippingRequirement($(v));
})

$(document).on('change', 'select[name="free_shipping_requirement"]', function() {
    freeShippingRequirement(this);
})

function freeShippingRequirement(parent) {
    if ($(parent).val() != '' && $(parent).val() != 'coupon') {
        $(parent).closest('.form-group').siblings('.free_shipping_condition').removeClass('d-none');
    } else {
        $(parent).closest('.form-group').siblings('.free_shipping_condition').addClass('d-none');
    }
}

function changeSetting(parent) {
    $(parent).closest('.parent').find('.warning-message').addClass('alert-secondary');
    $(parent).closest('.parent').find('.warningMessage').slideDown(300);
    $(parent).closest('.parent').find('#warning-msg').html(jsLang('Settings have changed, you should save them!'));
}

function successNotification(parent, message) {
    parent.find('.abc').addClass('alert-success');
    parent.find('.warningMessage').slideDown();
    parent.find('.msg').html(message);
}

function failNotification(parent, message) {
    parent.find('.abc').addClass('alert-danger');
    parent.find('.warningMessage').slideDown();
    parent.find('.msg').html(message);
}

function errorNotification(parent, data) {
    parent.find('.abc').addClass('alert-danger');
    parent.find('.warningMessage').slideDown();
    $.each(data.responseJSON.errors, function(key,value) {
        parent.find('.msg').html(value);
    });
}

function timeoutNotification(parent) {
    setTimeout(() => {
        parent.closest('.parent').find('.warningMessage').slideUp(500),
         setTimeout(() => {
            parent.closest('.parent').find('.abc').removeClass('alert-success alert-danger')
         }, 501);
    }, 5000);
}

// Show message when you have change a field
$(document).on('change', "input, select", function() {
    changeSetting(this);
});

// Remove location
$(document).on('click', '.action-btn', function() {
    changeSetting(this);
    $(this).closest('tr').remove();
})

$(document).on('click', '.add-new-location', function() {
    $(this).closest('tr').before($('.add-new-location-data tbody').html());
    $(this).closest('tbody').find("tr td:contains('No location found.')").closest('tr').remove();
})

$(document).on('click', '.add-new-class', function() {
    $(this).closest('tr').before($('.add-new-class-data tbody').html());
    $(this).closest('tbody').find('td:contains("No shipping class found.")').closest('tr').remove();
})

// Remove shipping zone
$(document).on('click', '.delete-button', function () {
    $(this).closest('.accordion').remove();
})

var shippingZoneSaveCount = 0;
function saveShippingZone(main) {
    shippingZoneSaveCount++;
    if (shippingZoneSaveCount > 1) {
        return false;
    }

    var parent = $(main).closest('.parent');
    var arr = new Array();
    var btn = main;

    $(main).text(jsLang('Saving')).append(`<div class="spinner-border spinner-border-sm ml-2" role="status">`)

    $(main).closest('.accordion-parent').find('.accordion').each((k, value) => {
        var obj = new Object();
        $(value).find('select, input').each((k, v) => {
            var name = $(v).attr('name')
            if (name != 'country' && name != 'city' && name != 'state' && name != 'zip') {
                obj[$(v).attr('name')] = $(v).val();
            }
        })
        var location = new Array();
        $(value).find('table tr').each((k, tr) => {
            var locationObj = new Object();
            $(tr).find('input').each((k, location) => {
                locationObj[$(location).attr('name')] = $(location).val();
            })

            if (Object.keys(locationObj).length > 0) {
                location.push(locationObj);
            }

        })
        obj['location'] = location;

        var classes = new Array();
        $(value).find('.shipping_classes .class').each((k, shipping_class) => {
            var classesObj = new Object();
            $(shipping_class).find('select, input').each((k, classes) => {
                classesObj[$(classes).attr('name')] = $(classes).val();
            })

            if (Object.keys(classesObj).length > 0) {
                classes.push(classesObj);
            }
        })
        obj['classes'] = classes;
        arr.push(obj);
    })

    $.ajax({
        url: SITE_URL + '/shipping-zone/store',
        type: 'POST',
        data: {
            '_token': token,
            'data': JSON.stringify(arr)
        },
        dataType: 'JSON',
        success: function (data) {
            if (data['status'] == 'success') {
                successNotification(parent, data.message);
                $('.save-shipping-zone').trigger('click');
                if ($(btn).closest('.accordion-parent').find('.accordion').length) {
                    $(btn).closest('div').siblings('.no_shipping_zone').remove();
                } else {
                    $(btn).closest('div').before(`
                        <div class="border p-2 no_shipping_zone">
                            <h4 class="text-center">${jsLang('No shipping zone found.')}</h4>
                        </div>
                    `);
                }

            } else {
                failNotification(parent, data.message);
            }
        },
        error: function(data) {
            errorNotification(parent, data);
        },
        complete: function(data) {
            $(btn).text(jsLang('Save')).find('.spinner-border').remove();
            shippingZoneSaveCount = 0;
        }
    });
    timeoutNotification(parent);
}
// shipping method
$(document).on('click', '.nav-list-button', function() {

	var tabID = $(this).attr('data-tab');

	$(this).addClass('active').siblings().removeClass('active');

	$('#tab-'+tabID).addClass('active').siblings().removeClass('active');
});

// Add shipping zone
$(document).on('click', '.add-shipping-zone', function() {

    $(this).closest('div').siblings('.no_shipping_zone').remove();
    var variable = ['main', 'location', 'method', 'flat', 'local', 'free'];
    var rand = {};
    for (const key in variable) {
        rand[variable[key]] = Math.floor(Math.random() * 10000000);
    }

    var data = `
    <div id="content-${rand.main}">
        ${$('.new-zone-content').html()}
    </div>
`;
$(this).closest('div').before(data);
$('#content-' + rand.main).find('input[name="id"]').val(rand.main);
$('#content-' + rand.main).find('.location-btn').attr('data-bs-target', '#flush-collapse-' + rand.location).closest('.accordion').attr('id', 'accordionFlush-' + rand.main).find('#flush-collapseTwo').attr({'id': 'flush-collapse-' + rand.location, 'data-bs-parent': '#accordionFlush-' + rand.main});
$('#content-' + rand.main).find('.method-btn').attr('data-bs-target', '#flush-collapse-' + rand.method).closest('.accordion').find('#flush-collapseOne').attr({'id': 'flush-collapse-' + rand.method, 'data-bs-parent': '#accordionFlush-' + rand.main});
$('#content-' + rand.main).find('.methods .free-shipping').attr({'href': '#free_shipping-' + rand.free, 'aria-controls': 'free_shipping-' + rand.free}).closest('div').find('#free_shipping').attr('id', 'free_shipping-' + rand.free);
$('#content-' + rand.main).find('.methods .local-pickup').attr({'href': '#local_pickup-' + rand.local, 'aria-controls': 'local_pickup-' + rand.local}).closest('div').find('#local_pickup').attr('id', 'local_pickup-' + rand.local);
$('#content-' + rand.main).find('.methods .flat-rate').attr({'href': '#flat_rate-' + rand.flat, 'aria-controls': 'flat_rate-' + rand.flat}).closest('div').find('#flat_rate').attr('id', 'flat_rate-' + rand.flat);
$('#content-' + rand.main).find('#flat_rate_status').attr('id', 'flat_rate_status-' + rand.main).siblings('label').attr('for', 'flat_rate_status-' + rand.main);
$('#content-' + rand.main).find('#local_pickup_status').attr('id', 'local_pickup_status-' + rand.main).siblings('label').attr('for', 'local_pickup_status-' + rand.main);
$('#content-' + rand.main).find('#free_shipping_status').attr('id', 'free_shipping_status-' + rand.main).siblings('label').attr('for', 'free_shipping_status-' + rand.main);
})

// Save shipping zone

$(document).on('click', '.save-shipping-zone', function() {
    saveShippingZone(this);
})

// Save shipping class
var shippingClassClickCount = 0;
$(document).on('click', '.save-class', function() {
    shippingClassClickCount++;
    if (shippingClassClickCount > 1) {
        return false;
    }

    var parent = $(this).closest('.parent');
    var tr = $(this).closest('tr');
    var url = SITE_URL + '/shipping-class/store';
    var btn = this;
    var arr = new Array();

    $(this).text(jsLang('Saving')).append(`<div class="spinner-border spinner-border-sm ml-2" role="status">`)
    $(this).closest('tbody').find('tr').each((key, value) => {
        if ($(value).find('input, select').length > 2) {
            var obj = new Object();
            $(value).find('input, select').each((k, v) => {
                obj[$(v).attr('name')] = filterXSS($(v).val());
            })
            arr.push(obj);
        }
    });

    $.ajax({
        url: url,
        type: 'POST',
        data: {
            '_token': token,
            'data': arr
        },
        dataType: 'JSON',
        success: function (data) {
            if (data['status'] == 'success') {
                successNotification(parent, data.message);

                $(btn).closest('tbody').find('td:contains("No shipping class found.")').closest('tr').remove();
                if ($(btn).closest('tbody').find('tr').length <= 2) {
                    $(btn).closest('tr').before(`
                        <tr>
                            <td class="text-center" colspan="5">${jsLang('No shipping class found.')}</td>
                        </tr>
                    `)
                }

                var zoneClassList = new Array();
                $('.shipping_classes:first').find('input, select').each((k, v) => {
                    if ($(v).attr('name') == 'slug') {
                        zoneClassList.push($(v).val());
                    }
                })
                // Add new class in flat rate if not exist when main class saved
                var mainClassList = new Array();
                for (const key in arr) {
                    mainClassList.push(arr[key].slug);
                    if (!zoneClassList.includes(arr[key].slug) && arr[key].slug != '') {
                        if ($('.flat_rateama koleksi melanggar {collections_rejected_policy_healthcare}.","3TyOB":"Kebijakan Perdagangan WhatsApp untuk Model bisnis, barang, item, atau layanan yang kami nilai mungkin atau memang mengandung kecurangan, menyesatkan, menyinggung, atau menipu, atau mungkin bersifat eksploitatif, tidak pantas, atau memberikan tekanan yang tidak semestinya terhadap kelompok yang disasar","1icG7s":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_misleading}","4gURah":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_misleading}.","2XnVq2":"Kebijakan Perdagangan WhatsApp untuk Mata uang nyata, virtual, atau palsu","2F8xUp":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_real_fake_currency}","3mr0Qt":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_real_fake_currency}.","2mvwqB":"Kebijakan Perdagangan WhatsApp untuk Layanan digital dan langganan",W6Ct0:"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_digital_services_products}","4gcYAt":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_digital_services_products}.","48SSD":"Kebijakan Perdagangan WhatsApp untuk Pelanggaran Pihak Ketiga","2S7xKD":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_thrid_party_infringements}","1UXaIr":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_thrid_party_infringements}.","2ldhoF":"Kebijakan Perdagangan WhatsApp untuk Produk atau barang yang memfasilitasi atau mendorong akses tidak sah ke media digital","2oPAST":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_unauthorized_media}",c5Zjc:"Kebijakan Perdagangan WhatsApp untuk Produk atau layanan ilegal","41Oqb6":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_illegal_products}","46TgAV":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_illegal_products}.",smMcx:"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_hazardous_goods_and_materials}",SdRo5:"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_hazardous_goods_and_materials}.","1kuvvN":"Kebijakan Perdagangan WhatsApp","4mvVou":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_stolen_goods}","1Zd6PC":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_stolen_goods}.","4yXqyf":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_human_exploitation_and_sexual_services}","3XImZQ":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_human_exploitation_and_sexual_services}.","1x5ZqO":"Ketentuan Bisnis WhatsApp","2AoLET":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_community_standards}",apmZF:"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_community_standards}.","4gmrAW":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_discrimination}","3j6FXO":"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_discrimination}.","3AiJjt":"Koleksi ini tidak disetujui karena nama koleksi melanggar {collections_rejected_policy_other_violation}",A2Pf5:"Kami telah meninjau kembali koleksi ini dan mengonfirmasi bahwa nama koleksi melanggar {collections_rejected_policy_other_violation}.","1VWu7A":"Koleksi","2RmHUB":"Setelan",M36Dw:"Tautan katalog","450jnd":"Lihat selengkapnya","3gdovw":"Tambah produk","3Ky71N":"Produk",RZBg9:"Gagal memperbarui setelan.",iTzgD:"Nyalakan fitur ini untuk memungkinkan pelanggan menambahkan item ke keranjang saat berbelanja di katalog Anda. Mematikan fitur ini tidak akan memengaruhi keranjang yang telah Anda terima sebelumnya. {learn_more_link}","1mbCt1":"Tambah ke keranjang","2m7e5W":"Nyala","1l0P5X":"Mati","2UxoQh":"Gagal memuat setelan katalog.","1RIlvV":"Tautan produk","2JHvi1":"Informasi selengkapnya","1jgCyX":"Laporkan produk","2iihM5":"Tambah katalog","354QjL":"Lihat atau edit katalog","3MHbpW":"Bagikan","1TSS6f":"Tampilkan item",a7Kx1:"Sembunyikan item",hOzFs:"Tambah ke keranjang","336mVv":"Hapus","3JLPDQ":"Item telah disembunyikan","1hLRT1":"Detail","4gOwiz":"Item ini habis","2A3GqV":"Lainnya","3jgAJS":"Kirim Pesan ke Bisnis","2DvSvh":"Baca selengkapnya","11vQtK":"Hapus gambar nomor {imageNumber}","1zIuu3":"Tambah gambar tambahan","3yrGBr":"Tambah gambar","2ra3Nx":"Anda telah mencapai jumlah maksimum gambar untuk ditambahkan.","1MclQl":"Item di pesan ini tidak lagi tersedia","3gfW35":"Nama importir",Kxo8I:"Alamat importir","3d8zl4":"Ubah","4tHAo0":"Tidak tersedia di {area-postcode}.","3YosHQ":"Habis","43JEsD":"Tidak tersedia di area kode pos Anda","3IDG0p":"Laporkan produk ini?","2CRdfl":"Kami akan meninjau produk ini dan jika tidak mengikuti panduan kami, produk ini akan dihapus.","3XLjyb":"Laporkan Produk","4qusYv":"Melaporkan produk","1vRsPv":"Terima kasih atas umpan balik Anda","40tL3T":"Laporan Anda telah dikirim.","11JN2U":"Tidak dapat melaporkan produk","3UgUaa":"Mohon pilih alasan.","2fZHoO":"Jelaskan lebih lanjut","3npRPm":"Kirim","2ifaot":"Gambar atau deskripsi tidak cocok dengan item",hjxQJ:"Ini adalah spam","3xz9jr":"Ini adalah penyalahgunaan, berbahaya, atau ilegal","2iFRd8":"Ini adalah penipuan",UiFcs:"Ini sepertinya barang tiruan atau palsu","1Kn4LY":"Lainnya","3C7pQj":"Kirim produk",BiYcl:"Kirim katalog",gXzTG:"Balas cepat dihapus","3yXE4I":"Gagal menghapus balas cepat","26SlCG":"Hapus balas cepat","38ji3u":"Anda yakin ingin menghapus balas cepat?",O1WFg:"Edit","1XMCRZ":"Hapus",NEJNy:"Lakukan panggilan suara dan video grup, gunakan aplikasi yang lebih cepat, dan banyak lagi.","2alUYB":"Unduh sekarang","38Tsvq":"Memperkenalkan WhatsApp desktop versi baru","1oSNe1":"Toko Anda memerlukan tindakan","1BPvof":"Untuk mempertahankan toko di WhatsApp, beralihlah ke katalog.","4nrF4T":"Pelajari selengkapnya","300jSy":"Komputer tidak terhubung","3eFdpv":"Memuat pesan ({percentage}%)",SKjEU:"Pesan sedang dimuat. Anda dapat terus menunggu, atau keluar dan menautkan ulang dengan telepon.","4u0X1o":"Pastikan telepon Anda memiliki koneksi Internet aktif.","3Whn0S":"Telepon tidak terhubung","3XIG3f":"Pembaruan tersedia","1d3TeN":"Dapatkan notifikasi pesan baru","3ZgKZU":"Buka blokir","3ZqbXj":"Blokir","2EThOp":"Anda hanya dapat menyematkan maksimal 3 chat","1C9Xfc":"Lepaskan Semua","33NEQP":"Beberapa chat Anda yang disematkan tidak dapat dilihat di perangkat ini. Untuk menyematkan chat yang berbeda, lepaskan semua chat terlebih dahulu.","2wu1x7":"Tetapkan chat","3QX44B":"Edit label","2rxtMR":"Buka menu konteks chat","3ikZTf":"Admin Komunitas","2CfYOt":"Kreator Komunitas","49cDew":"Admin Grup","4yZipP":"Diundang",FRvtZ:"Chat dibisukan","2UzXVt":"pesan @","1nVFgF":"Pengumuman","4APShH":"Anda memberi suara di:",gJpmm:"Anda bereaksi {emoji} pada:",hyQZE:"{user-name} memberi suara di:","1n8nAQ":"{user-name} bereaksi {emoji} pada:","4cEZdg":"Memberi suara di:","27ZYlH":"Bereaksi {emoji} pada:","1eYigB":"Diblokir. Klik untuk membuka blokir.","1PK2es":"Diarsipkan","2jWpt9":"Kontak","3YbGYw":"Kontak di WhatsApp",YrVyI:"Grup yang Anda adalah adminnya","4bUoVs":"Grup","2oGAZo":"Kirim pesan ke diri sendiri","284Coh":"Kontak diblokir","1Ftlt":"Grup ini sudah tidak tersedia","46OC7g":{"*":"{contacts-list} juga ada di grup ini",_1:"{contacts-list} juga ada di grup ini"},"46DIdr":"Chat","46ZyXR":"Difilter berdasarkan belum dibaca","1QkAAV":"Tindakan","1ycJwZ":"Komunitas","1Zpffq":"Setelan","2WB4P8":"Grup yang sama","4wVbNG":"Pesan berbintang","4ECKrT":"Pesan tersimpan","2b6bp3":"DITETAPKAN KEPADA ANDA","28LBod":"Hasil pencarian.",iikD9:"Daftar chat","4uA4Xh":"Semua","4aVUCt":"Belum dibaca","1rjDkx":"Pribadi","4lfk8V":"Bisnis",Xb7yl:"Toko","8mkiP":"Katalog","2FNCEZ":"Chat Baru",lLS8i:"Cari","2OkL2n":"Katalog dinonaktifkan","2J2Znq":"Saat ini Anda tidak dapat memulihkan katalog kare