/* 	$Id$	 */

tinyMCE.init({
    mode : "textareas",
        theme: "advanced",
        theme_advanced_disable: "image,anchor,newdocument,visualaid", 
        theme_advanced_buttons2_add: "separator,tablecontrols",
        theme_advanced_buttons3_add: "cut,copy,pasteword,pastetext,selectall,preview,fullscreen,print",
        inline_styles: true,
        convert_newlines_to_brs: true,
        plugins: "paste,fullscreen,preview,print,table",
        paste_use_dialog: false,
        paste_auto_cleanup_on_paste: true,     
        convert_fonts_to_spans : true,
        paste_strip_class_attributes : "all",
        fullscreen_settings : {
        theme_advanced_path_location : "top"
            },
        plugin_preview_width : "500",
            plugin_preview_height : "600",
            //doctype: '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">',
            verify_html: true
        });
