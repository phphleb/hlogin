<?php
use Phphleb\Hlogin\App\Main as Main;
if ($this->type == 'ContactMessage') {
    $contact = Main::getConfigContact();
    if($contact['data']['active'] !== "on") {
        hleb_bt3e3gl60pg8h71e00jep901_error_404();
    }
}
?><!DOCTYPE html>
<html lang="<?= Hleb\Constructor\Handlers\Request::get('lang');  ?>">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width" />
    <!-- Optional parameters -->
    <?= $this->insertedCode ?>

    <style>
        .hlogin-p-register-popup-global {
            width: 100%!important;
            height: 100% !important;
            left:0!important;
            top:0!important;
            max-height: 100%!important;
        }
        .hlogin-p-close-popup-x {
            display:none!important;
            opacity: 0!important;
            visibility: hidden;
        }
        .hlogin-p-overlay-fon-from-popup {
            display:none;
            opacity: 0;
            visibility: hidden;
        }
        body {
            background-color: whitesmoke;
        }
        .hlogin-only-single-page {
            display: block!important;
        }
        #JsWarning img {
            width: 100%!important;
            max-width: 450px!important;
        }
    </style>
    <title><?=  $this->title ?? 'Authorization';  ?></title>
</head>
<body>
<div id="JsWarning" align="center"><img src="/en/login/resource/<?= rand(10, 10000000); ?>/all/images/gif/jswarning/" width="450" height="50" alt="JS Disabled info"></div>
<script>
    window.onload = function(){
        var intervalID = setInterval(function() {
            if(typeof hloginVariableOpenPopup !== 'undefined' ) {
                clearInterval(intervalID);
                document.getElementById('JsWarning').style.display = 'none';
                hloginVariableOpenPopup('<?= $this->type; ?>');
                var intervalFonID = setInterval(function() {
                    var f = document.querySelector('.hlogin-p-overlay-fon-from-popup');
                    if(f != null) {
                        clearInterval(intervalFonID);
                        f.style.visibility = 'visible';
                        f.style.display = 'block';
                        f.style.opacity = '1';
                    }
                    }, 1000);
            }
        }, 20);
    }
</script>
<?php
hleb_e0b1036c1070101_template('hlogin/templates/add');
?>

</body></html>


