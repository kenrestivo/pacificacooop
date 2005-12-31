/* 	$Id$	 */

tinyMCE.init({
  mode : "textareas",
  theme: "advanced",
  theme_advanced_disable: "image,anchor,newdocument,visualaid,link,unlink,code", 
  theme_advanced_buttons3_add: "cut,copy,pasteword,pastetext,selectall",
  convert_newlines_to_brs: true,
  plugins: "paste",
  paste_use_dialog: false,
  paste_auto_cleanu4p_on_paste: true,     
  paste_strip_class_attributes : "all",
  verify_html: true
 });
