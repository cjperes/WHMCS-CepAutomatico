<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

add_hook("ClientAreaFooterOutput", 1, function ($vars) {

    if ($vars["filename"] == "register" || $vars["filename"] == "cart") {
        require_once ('version.php');

        $javascript = '
<script type="text/javascript">
        var _0x29a8=["\x76\x61\x6C","\x23\x69\x6E\x70\x75\x74\x43\x6F\x75\x6E\x74\x72\x79\x2C\x20\x23\x63\x6F\x75\x6E\x74\x72\x79","\x42\x52","\x68\x69\x64\x65","\x23\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x31\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x31\x5D\x2C\x20\x2E\x66\x61\x2D\x62\x75\x69\x6C\x64\x69\x6E\x67\x2D\x6F\x2C\x20\x23\x61\x64\x64\x72\x65\x73\x73\x31\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x61\x64\x64\x72\x65\x73\x73\x31\x5D","\x23\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x32\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x32\x5D\x2C\x20\x2E\x66\x61\x2D\x6D\x61\x70\x2D\x6D\x61\x72\x6B\x65\x72\x2C\x20\x23\x61\x64\x64\x72\x65\x73\x73\x32\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x61\x64\x64\x72\x65\x73\x73\x32\x5D","\x23\x69\x6E\x70\x75\x74\x43\x69\x74\x79\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x69\x6E\x70\x75\x74\x43\x69\x74\x79\x5D\x2C\x20\x2E\x66\x61\x2D\x62\x75\x69\x6C\x64\x69\x6E\x67\x2D\x6F\x2C\x20\x23\x63\x69\x74\x79\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x63\x69\x74\x79\x5D","\x23\x73\x74\x61\x74\x65\x73\x65\x6C\x65\x63\x74\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x69\x6E\x70\x75\x74\x53\x74\x61\x74\x65\x5D\x2C\x20\x2E\x66\x61\x2D\x6D\x61\x70\x2D\x73\x69\x67\x6E\x73\x2C\x20\x6C\x61\x62\x65\x6C\x5B\x66\x6F\x72\x3D\x73\x74\x61\x74\x65\x5D","","\x23\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x31","\x23\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x32","\x23\x69\x6E\x70\x75\x74\x43\x69\x74\x79","\x23\x73\x74\x61\x74\x65\x73\x65\x6C\x65\x63\x74","\x72\x65\x70\x6C\x61\x63\x65","\x74\x65\x73\x74","\x2E\x2E\x2E","\x23\x61\x64\x64\x72\x65\x73\x73\x31","\x23\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x32\x2C\x20\x23\x61\x64\x64\x72\x65\x73\x73\x32","\x23\x69\x6E\x70\x75\x74\x43\x69\x74\x79\x2C\x20\x23\x63\x69\x74\x79","\x68\x74\x74\x70\x73\x3A\x2F\x2F\x76\x69\x61\x63\x65\x70\x2E\x63\x6F\x6D\x2E\x62\x72\x2F\x77\x73\x2F","\x2F\x6A\x73\x6F\x6E\x2F\x3F\x63\x61\x6C\x6C\x62\x61\x63\x6B\x3D\x3F","\x65\x72\x72\x6F","\x6C\x6F\x67\x72\x61\x64\x6F\x75\x72\x6F","\x23\x69\x6E\x70\x75\x74\x41\x64\x64\x72\x65\x73\x73\x31\x2C\x20\x23\x61\x64\x64\x72\x65\x73\x73\x31","\x62\x61\x69\x72\x72\x6F","\x6C\x6F\x63\x61\x6C\x69\x64\x61\x64\x65","\x75\x66","\x73\x68\x6F\x77","\x43\x45\x50\x20\x6E\xE3\x6F\x20\x65\x6E\x63\x6F\x6E\x74\x72\x61\x64\x6F\x2E","\x67\x65\x74\x4A\x53\x4F\x4E","\x46\x6F\x72\x6D\x61\x74\x6F\x20\x64\x65\x20\x43\x45\x50\x20\x69\x6E\x76\xE1\x6C\x69\x64\x6F\x2E","\x62\x6C\x75\x72","\x23\x69\x6E\x70\x75\x74\x50\x6F\x73\x74\x63\x6F\x64\x65\x2C\x20\x23\x70\x6F\x73\x74\x63\x6F\x64\x65","\x72\x65\x61\x64\x79"];var _0xa8aa=[_0x29a8[0],_0x29a8[1],_0x29a8[2],_0x29a8[3],_0x29a8[4],_0x29a8[5],_0x29a8[6],_0x29a8[7],_0x29a8[8],_0x29a8[9],_0x29a8[10],_0x29a8[11],_0x29a8[12],_0x29a8[13],_0x29a8[14],_0x29a8[15],_0x29a8[16],_0x29a8[17],_0x29a8[18],_0x29a8[19],_0x29a8[20],_0x29a8[21],_0x29a8[22],_0x29a8[23],_0x29a8[24],_0x29a8[25],_0x29a8[26],_0x29a8[27],_0x29a8[28],_0x29a8[29],_0x29a8[30],_0x29a8[31],_0x29a8[32],_0x29a8[33]];$(document)[_0xa8aa[33]](function(){if($(_0xa8aa[1])[_0xa8aa[0]]()== _0xa8aa[2]){$(_0xa8aa[4])[_0xa8aa[3]]();$(_0xa8aa[5])[_0xa8aa[3]]();$(_0xa8aa[6])[_0xa8aa[3]]();$(_0xa8aa[7])[_0xa8aa[3]]()};function _0x484ax2(){$(_0xa8aa[9])[_0xa8aa[0]](_0xa8aa[8]);$(_0xa8aa[10])[_0xa8aa[0]](_0xa8aa[8]);$(_0xa8aa[11])[_0xa8aa[0]](_0xa8aa[8]);$(_0xa8aa[12])[_0xa8aa[0]](_0xa8aa[8]);$(_0xa8aa[4])[_0xa8aa[3]]();$(_0xa8aa[5])[_0xa8aa[3]]();$(_0xa8aa[6])[_0xa8aa[3]]();$(_0xa8aa[7])[_0xa8aa[3]]()}$(_0xa8aa[32])[_0xa8aa[31]](function(){var _0x484ax3=$(this)[_0xa8aa[0]]()[_0xa8aa[13]](/\D/g,_0xa8aa[8]);if(_0x484ax3!= _0xa8aa[8]){var _0x484ax4=/^[0-9]{8}$/;if(_0x484ax4[_0xa8aa[14]](_0x484ax3)){$(_0xa8aa[16])[_0xa8aa[0]](_0xa8aa[15]);$(_0xa8aa[17])[_0xa8aa[0]](_0xa8aa[15]);$(_0xa8aa[18])[_0xa8aa[0]](_0xa8aa[15]);$(_0xa8aa[12])[_0xa8aa[0]](_0xa8aa[15]);$[_0xa8aa[29]](_0xa8aa[19]+ _0x484ax3+ _0xa8aa[20],function(_0x484ax5){if(!(_0xa8aa[21] in  _0x484ax5)){$(_0xa8aa[23])[_0xa8aa[0]](_0x484ax5[_0xa8aa[22]]);$(_0xa8aa[17])[_0xa8aa[0]](_0x484ax5[_0xa8aa[24]]);$(_0xa8aa[18])[_0xa8aa[0]](_0x484ax5[_0xa8aa[25]]);$(_0xa8aa[12])[_0xa8aa[0]](_0x484ax5[_0xa8aa[26]]);$(_0xa8aa[4])[_0xa8aa[27]]();$(_0xa8aa[5])[_0xa8aa[27]]();$(_0xa8aa[6])[_0xa8aa[27]]();$(_0xa8aa[7])[_0xa8aa[27]]()}else {_0x484ax2();alert(_0xa8aa[28])}})}else {_0x484ax2();alert(_0xa8aa[30])}}else {_0x484ax2()}})})
    </script>';

        return $javascript;
    }
});
