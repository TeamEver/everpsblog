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
        var everTinyMceImageUploadHandler = function (blobInfo, success, failure, progress) {
            var uploadPromise = new Promise(function (resolve, reject) {
                if (!window.everpsblogTinyMceUploadUrl || !window.everpsblogTinyMceUploadToken) {
                    reject('Image upload is not configured.');
                    return;
                }

                var xhr = new XMLHttpRequest();
                xhr.open('POST', window.everpsblogTinyMceUploadUrl);
                xhr.withCredentials = true;
                xhr.upload.onprogress = function (event) {
                    if (event.lengthComputable && typeof progress === 'function') {
                        progress(event.loaded / event.total * 100);
                    }
                };
                xhr.onload = function () {
                    var json;
                    try {
                        json = JSON.parse(xhr.responseText || '{}');
                    } catch (error) {
                        reject('Invalid upload response.');
                        return;
                    }
                    if (xhr.status < 200 || xhr.status >= 300) {
                        reject(json.error || 'Image upload failed.');
                        return;
                    }
                    if (!json || typeof json.location !== 'string') {
                        reject('Invalid upload response.');
                        return;
                    }
                    resolve(json.location);
                };
                xhr.onerror = function () {
                    reject('Image upload failed.');
                };

                var formData = new FormData();
                formData.append('_legacy_token', window.everpsblogTinyMceUploadToken);
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            });

            if (typeof success === 'function') {
                uploadPromise.then(success).catch(function (message) {
                    if (typeof failure === 'function') {
                        failure(message);
                    }
                });

                return;
            }

            return uploadPromise;
        };

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
            automatic_uploads: true,
            paste_data_images: true,
            images_upload_handler: everTinyMceImageUploadHandler,
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
