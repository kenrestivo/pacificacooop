/* 	$Id$	 */

tinyMCE.init({
    mode : "textareas",
        theme: "advanced",
        theme_advanced_disable: "image,anchor,newdocument,visualaid,code", 
        theme_advanced_buttons3_add: "cut,copy,pasteword,pastetext,selectall,preview,fullscreen,print",
        inline_styles: true,
        convert_newlines_to_brs: true,
        plugins: "paste,fullscreen,preview,print",
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
            verify_html: true,
            valid_elements : "" 
        +"a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name"

        +"|rel|rev"
        +"|shape<circle?default?poly?rect|style|tabindex|title|target|type],"
        +"abbr[class|dir<ltr?rtl|id|lang|style|title],"
        +"acronym[class|dir<ltr?rtl|id|id|lang|style|title],"
        +"address[class|align|dir<ltr?rtl|id|lang|style|title],"
        +"area[accesskey|alt|class|coords|dir<ltr?rtl|href|id|lang|nohref<nohref"
        +"|shape<circle?default?poly?rect|style|tabindex|title|target],"
        +"base[href|target],"
        +"basefont[color|face|id|size],"
        +"bdo[class|dir<ltr?rtl|id|lang|style|title],"
        +"big[class|dir<ltr?rtl|id|lang|style"
        +"|title],"
        +"blockquote[dir|style|cite|class|dir<ltr?rtl|id|lang|style|title],"
        +"body[alink|background|bgcolor|class|dir<ltr?rtl|id|lang|link|style|title|text|vlink],"
        +"br[class|clear<all?left?none?right|id|style|title],"
        +"button[accesskey|class|dir<ltr?rtl|disabled<disabled|id|lang|name|style|tabindex|title|type|value],"
        +"caption[align<bottom?left?right?top|class|dir<ltr?rtl|id|lang|style|title],"
        +"center[class|dir<ltr?rtl|id|lang|style|title],"
        +"cite[class|dir<ltr?rtl|id|lang|style|title],"
        +"code[class|dir<ltr?rtl|id|lang|style|title],"
        +"col[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
        +"|lang|span|style|title"
        +"|valign<baseline?bottom?middle?top|width],"
        +"colgroup[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl"
        +"|id|lang|span|style|title"
        +"|valign<baseline?bottom?middle?top|width],"
        +"dd[class|dir<ltr?rtl|id|lang|style|title],"
        +"del[cite|class|datetime|dir<ltr?rtl|id|lang|style|title],"
        +"dfn[class|dir<ltr?rtl|id|lang|style|title],"
        +"dir[class|compact<compact|dir<ltr?rtl|id|lang|style|title],"
        +"div[class|dir<ltr?rtl|id|lang|style|title],"
        +"dl[class|compact<compact|dir<ltr?rtl|id|lang|style|title],"
        +"dt[class|dir<ltr?rtl|id|lang|style|title],"
        +"em/i[class|dir<ltr?rtl|id|lang|style"
        +"|title],"
        +"fieldset[class|dir<ltr?rtl|id|lang|style"
        +"|title],"
        +"font[class|color|dir<ltr?rtl|face|id|lang|size|style|title],"
        +"form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang"
        +"|method<get?post|name|style|title|target],"
        +"frame[class|frameborder|id|longdesc|marginheight|marginwidth|name"
        +"|noresize<noresize|scrolling<auto?no?yes|src|style|title],"
        +"frameset[class|cols|id|onload|onunload|rows|style|title],"
        +"h1[class|dir<ltr?rtl|id|lang|style|title],"
        +"h2[class|dir<ltr?rtl|id|lang|style|title],"
        +"h3[class|dir<ltr?rtl|id|lang|style|title],"
        +"h4[class|dir<ltr?rtl|id|lang|style|title],"
        +"h5[class|dir<ltr?rtl|id|lang|style|title],"
        +"h6[class|dir<ltr?rtl|id|lang|style|title],"
        +"hr[class|dir<ltr?rtl|id|lang|noshade<noshade|size|style|title|width],"
        +"html[dir<ltr?rtl|lang|version],"
        +"iframe[align<bottom?left?middle?right?top|class|frameborder|height|id"
        +"|longdesc|marginheight|marginwidth|name|scrolling<auto?no?yes|src|style"
        +"|title|width],"
        +"img[align<bottom?left?middle?right?top|alt|border|class|dir<ltr?rtl|height"
        +"|hspace|id|ismap<ismap|lang|longdesc|name|src|style|title|usemap|vspace|width],"
        +"input[accept|accesskey|align<bottom?left?middle?right?top|alt"
        +"|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang"
        +"|maxlength|name"
        +"|readonly<readonly|size|src|style|tabindex|title"
        +"|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text"
        +"|usemap|value],"
        +"ins[cite|class|datetime|dir<ltr?rtl|id|lang|style|title],"
        +"isindex[class|dir<ltr?rtl|id|lang|prompt|style|title],"
        +"kbd[class|dir<ltr?rtl|id|lang|style|title],"
        +"label[accesskey|class|dir<ltr?rtl|for|id|lang|onblur|style|title],"
        +"legend[align<bottom?left?right?top|accesskey|class|dir<ltr?rtl|id|lang"
        +"|style|title],"
        +"li[class|dir<ltr?rtl|id|lang|style|title|type|value],"
        +"link[charset|class|dir<ltr?rtl|href|hreflang|id|lang|rel|rev|style|title|target|type],"
        +"map[class|dir<ltr?rtl|id|lang|name|style|title],"
        +"menu[class|compact<compact|dir<ltr?rtl|id|lang|style|title],"
        +"noframes[class|dir<ltr?rtl|id|lang|style"
        +"|title],"
        +"noscript[class|dir<ltr?rtl|id|lang|style"
        +"|title],"
        +"ol[class|compact<compact|dir<ltr?rtl|id|lang|style|title],"
        +"optgroup[class|dir<ltr?rtl|disabled<disabled|id|label|lang|style|title],"
        +"option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|selected<selected|style|title|value],"
        +"p[class|dir<ltr?rtl|id|lang|style|title],"
        +"param[id|name|type|value|valuetype<DATA?OBJECT?REF],"
        +"pre/listing/plaintext/xmp[align|class|dir<ltr?rtl|id|lang|style|title|width],"
        +"q[cite|class|dir<ltr?rtl|id|lang|style|title],"
        +"s[class|dir<ltr?rtl|id|lang|style|title],"
        +"samp[class|dir<ltr?rtl|id|lang|style|title],"
        +"script[charset|defer|language|src|type],"
        +"select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name"
        +"|size|style|tabindex|title],"
        +"small[class|dir<ltr?rtl|id|lang|style|title],"
        +"span[align|class|class|dir<ltr?rtl|id|lang|style|title],"
        +"strike[class|class|dir<ltr?rtl|id|lang|style|title],"
        +"strong/b[class|dir<ltr?rtl|id|lang|style|title],"
        +"style[dir<ltr?rtl|lang|media|title|type],"
        +"sub[class|dir<ltr?rtl|id|lang|style|title],"
        +"sup[class|dir<ltr?rtl|id|lang|style|title],"
        +"table[bgcolor|border|cellpadding|cellspacing|class"
        +"|dir<ltr?rtl|frame|height|id|lang|rules"
        +"|style|summary|title|width],"
        +"tbody[char|class|charoff|dir<ltr?rtl|id"
        +"|lang|style|title"
        +"|valign<baseline?bottom?middle?top],"
        +"td[abbr|axis|bgcolor|char|charoff|class"
        +"|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup"
        +"|style|title|valign<baseline?bottom?middle?top|width],"
        +"textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name"
        +"|readonly<readonly|rows|style|tabindex|title],"
        +"tfoot[align<center?char?justify?left?right|char|charoff|class|dir<ltr?rtl|id"
        +"|lang|style|title"
        +"|valign<baseline?bottom?middle?top],"
        +"th[abbr|axis|bgcolor|char|charoff|class"
        +"|colspan|dir<ltr?rtl|headers|height|id|lang|nowrap<nowrap|rowspan|scope<col?colgroup?row?rowgroup"
        +"|style|title|valign<baseline?bottom?middle?top|width],"
        +"thead[char|charoff|class|dir<ltr?rtl|id"
        +"|lang|style|title"
        +"|valign<baseline?bottom?middle?top],"
        +"tr[abbr|align<center?char?justify?left?right|bgcolor|char|charoff|class"
        +"|rowspan|dir<ltr?rtl|id|lang|style"
        +"|title|valign<baseline?bottom?middle?top],"
        +"tt[class|dir<ltr?rtl|id|lang|style|title],"
        +"ul[class|compact<compact|dir<ltr?rtl|id|lang|style|title|type],"
        +"var[class|dir<ltr?rtl|id|lang|style|title]"


        });
