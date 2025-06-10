/**
 * 2019-2025 Team Ever
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 *  @author    Team Ever <https://www.team-ever.com/>
 *  @copyright 2019-2025 Team Ever
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

var customTinyMCE = {
    init: function () {
        window.defaultTinyMceConfig = {
            menubar: true,
            statusbar: true,
            plugins : "visualblocks, preview searchreplace print insertdatetime, hr charmap colorpicker anchor code link image paste pagebreak table contextmenu filemanager table code media autoresize textcolor emoticons",
            toolbar2 : "newdocument,print,|,bold,italic,underline,|,strikethrough,superscript,subscript,|,forecolor,colorpicker,backcolor,|,bullist,numlist,outdent,indent",
            toolbar1 : "styleselect,|,formatselect,|,fontselect,|,fontsizeselect,",
            toolbar3 : "code,|,table,|,cut,copy,paste,searchreplace,|,blockquote,|,undo,redo,|,link,unlink,anchor,|,image,emoticons,media,|,inserttime,|,preview ",
            toolbar4 : "visualblocks,|,charmap,|,hr,",
            external_filemanager_path: ad+"/filemanager/",
            filemanager_title: "File manager" ,
            external_plugins: { "filemanager" : ad+"/filemanager/plugin.min.js"},
            language: iso,
            skin: "prestashop",
            statusbar: false,
            relative_urls : false,
            convert_urls: false,
            extended_valid_elements : "em[class|name|id]",
            menu: {
                edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall'},
                insert: {title: 'Insert', items: 'media image link | pagebreak'},
                view: {title: 'View', items: 'visualaid'},
                table: {title: 'Table', items: 'inserttable tableprops deletetable | cell row column'},
                tools: {title: 'Tools', items: 'code'}
            }
        }
    },
};
$(function () {
    customTinyMCE.init();
});