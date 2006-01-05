<?php 
chdir('members');  //XXX hack
require_once("members/public_blog.php"); 
$cp=& new CoopPage();
?>
<html>

<title>Pacifica Co-Op Nursery School</title>
<link href="main.css" rel="stylesheet" type="text/css">
<body>

<table width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse: collapse" bordercolor="#111111">
  <tr>
	  <td width="40" rowspan="2" valign="top">
    	<img src="side.gif" border="0">
    </td>
    <td valign="top" height="145">
    	<img src="heading.gif" border="0">
    </td>
  </tr>
  <tr>
    <td>
    	<h2>
    		<font size="5" color="#007800">
    			<b>Welcome to the Pacifica Co-op Nursery School!</b>
    		</font>
    	</h2>
    	<p>Pacifica Co-op Nursery School is a cooperative preschool where parents and 
    	children learn together; where parents learn about their children as their children 
    	learn about the world.</p>
    	<p>If you&#39;d like to learn more about our school or receive an application to 
    	our school, please call us at (650) 355-3272.</p>
    
	<hr size="5" width="600" color="red" align="left">
    
	<h2>
		<b><font size"+2">Breaking News!</font></b>
	</h2>
		<?php print getBlogEntries(&$cp);   ?>
	<hr size="5" width="600" color="red" align="left">
	<h2>
		<b><font size"+2">Upcoming Events!</font></b>
	</h2>
		<?php print getEvents(&$cp);   ?>
	<hr size="5" width="600" color="red" align="left">
	<h2>
    		<b><font size="+2">About Us</font></b>
    	</h2>
    	<p><b>Philosophy:</b> We believe that together as parents and staff we can enhance 
	growth, development and self-esteem, and promote a deeper understanding and 
    	respect of each child&#39;s unique qualities. See more <a href="philosophy.html">
    	here</a>.</p>
    	<p><b>Goal:</b> To provide an environment in which a child&#39;s social-emotional, 
    	cognitive and physical being will be enriched.</p>
    	<p><b>Objectives:</b> Our objective is to create an environment of learning 
    	for parents and children to practice numerous skills which will increase the 
    	cognitive, social-emotional and physical skills of each child, by providing 
    	hands-on experiences in a safe, socially stimulating and supervised environment.</p>
	<p><b>Our program:</b> Our program is thematic and multi-modal. Emphasis is 
    	placed on the process rather than on an end product. By thematic, we mean that 
    	information is presented within a theme (i.e. colors, outer space, safety, circus 
    	or winter celebrations) over two to three weeks. By multi-modal, we mean that 
    	the themes are presented through a variety of activities used to explore many 
    	facets of the theme as well as used to reinforce active learning process.</p>
<p><b>Non-Discrimination Policy:</b> The Pacifica Co-Op Nursery School admits children and their families regardless of   
their race, color, religion, nationality, ethnic origin or sexual orientation. </p>


    	<p>You can learn much more by reading the <a href="documents/current-handbook.pdf">Co-Op 
    	Handbook</a>. (requires Adobe Acrobat Reader, available <a href="http://www.adobe.com/products/acrobat/readstep2.html">here</a> for 
	free.) Also available are the Co-Op <a href="documents/CoopByLaws.pdf">By-laws</a>.</p>
    	
    	<hr size="5" width="600" color="red" align="left">
    	
    	<h2><b><font size="+2">Enrollment</font></b></h2>
	    <p>Is your child ready? Please see <a href="choosingforsuccess.html">Choosing 
    	for Success</a>. Be sure also to read the
    	<a href="admissionrequirements.html">admission requirements</a>.</p>
    	<p>See notes about admissions <a href="admissions.html">here</a>.</p>
    	<p>Download an application to get on the waiting list
    	<a href="./documents/Wait_list_application_2003.doc">here</a>.</p>
    	
    	<hr size="5" width="600" color="red" align="left">
    	
	<h2><b><font size="+2">Our Staff</font></b></h2>
	
    	<p>Director: Sandy Wallace</p>
    	<p>Asst. Directors: Diana Taur-McMillan &amp; Catherine Miller</p>
    
    	<hr size="5" width="600" color="red" align="left">
    	
    	<h2><font size="+2">More Information</font></h2>
    	<ul>
      		<li>
      			There are two major fundraisers for the year,
      			the Trike-a-Thon and Springfest.</li>
      		<li>
      			Learn about Bug School, our great <a href="summerprogram.html">Summer program</a>.
      		</li>
      		<li>
      			Be sure to check out our great new <a href="playground/playgroundproject.html">Neighborhood Playgarden</a>! It&#39;s open to the public during non-school hours; feel free to come visit!
      		</li>
    	</ul>
    
    	<hr size="5" width="600" color="red" align="left">
    
    	<h2><b><font size="5">Contact Us</font></b></h2>
    	<p><b>Pacifica Co-op Nursery School</b><br>
    	548 Carmel Avenue<br>
    	Pacifica, CA 94044</p>
    	<table border="0" cellpadding="0" cellspacing="0" bordercolor="#111111" width="282">
      		<tr>
        		<td width="100">Office:</td>
        		<td width="182">(650) 355-4465</td>
      		</tr>
      		<tr>
        		<td width="100">Enrollment:</td>
        		<td width="182">(650) 355-3272</td>
      		</tr>
    	</table>
	<hr size="5" width="600" color="red" align="left">
	&copy; 2003 Pacifica Co-op Nursery School
    </td>
  </tr>
</table>

</body>

</html>

