<?php  // $Id: index.php,v 1.20.2.7 2009/03/04 13:34:20 skodak Exp $

require_once('../../../config.php');
require_once($CFG->dirroot.'/lib/tablelib.php');


define('DEFAULT_PAGE_SIZE', 20);
define('SHOW_ALL_PAGE_SIZE', 5000);

$id         = required_param('id', PARAM_INT); // course id.

$roleid     = optional_param('roleid', 0, PARAM_INT); // which role to show
$instanceid = optional_param('instanceid', 0, PARAM_INT); // instance we're looking at.
$forumid    = optional_param('forum', 0, PARAM_INT);  // forum ID on a post back

$page       = optional_param('page', 0, PARAM_INT);                     // which page to show
$perpage    = optional_param('perpage', DEFAULT_PAGE_SIZE, PARAM_INT);  // how many per page


if (!$course = get_record('course', 'id', $id)) {
	print_error('invalidcourse');
}
if ($roleid != 0 and !$role = get_record('role', 'id', $roleid)) {
	print_error('invalidrole');
}

require_login($course);
$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('coursereport/viewforumactivity:view', $context);




// Log
add_to_log($course->id, "forum", "report viewforumactivity", "report/viewforumactivity/index.php?id=$course->id", $course->id);



// Build the breadcrumbs 

$strviewforumactivity   = get_string('viewforumactivity','coursereport_viewforumactivity');
$strreports       = get_string('reports');



$navlinks = array();
$navlinks[] = array('name' => $strreports, 'link' => "../../report.php?id=$course->id", 'type' => 'misc');
$navlinks[] = array('name' => $strviewforumactivity, 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);
print_header("$course->shortname: $strviewforumactivity", $course->fullname, $navigation);



// forums list for the course
$forums=get_records_select('forum', "type='general' AND course=".$course->id, 'name ASC', 'id, name');

$forumList = array();
foreach ( $forums as $forum) {
	$forumList[$forum->id] = format_string($forum->name);	
}



// print first controls.
echo '<form class="participationselectform" action="index.php" method="get"><div>'."\n".
	'<input type="hidden" name="id" value="'.$course->id.'" />'."\n";


echo 'Total number of forums : '. count($forums) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

echo '<label for="menuinstanceid">'.get_string('viewforumactivity','coursereport_viewforumactivity').'</label>'."\n";
choose_from_menu($forumList, 'forum');


helpbutton('participationreport',get_string('participationreport'));
echo '<input type="submit" value="'.get_string('go').'" />'."\n</div></form>\n";

$baseurl =  $CFG->wwwroot.'/course/report/viewforumactivity/index.php?id='.$course->id.'&amp;roleid='
	.$roleid.'&amp;instanceid='.$instanceid.'&amp;perpage='.$perpage;



        // SQL to extract selected forum
        $sql=" select mu.id, mu.LastName || ','|| mu.firstName AS username ,  count(*) As totalposts, 
                 sum(case when mfp.Parent=0 then 1 else 0 end) as topicposts,
                 Sum(case when mfp.Parent>0 then 1 else 0 end) as replies,
                 (Sum(mfr.rating) /Count(mfr.rating))   As avgratings ,
                 Count(mfr.rating) As cntratings,
                 mf.Scale as maxrating,
                 Sum(case when mfr.rating>0 then 1 else Null end) as numrating
                    from  {$CFG->prefix}user mu
                     Inner Join {$CFG->prefix}forum_posts mfp on mu.Id = mfp.userid
                     Inner Join {$CFG->prefix}forum_discussions mfd on mfp.discussion = mfd.ID
                     Inner Join {$CFG->prefix}forum mf On mfd.forum = mf.ID
                     Left Join {$CFG->prefix}forum_ratings mfr on mfr.Post = mfp.ID
                     where mfd.forum= $forumid
                     Group By mu.id, mu.LastName, mu.firstName , mf.Scale ";
                     

        // echo '<br />' . $sql . '<br />' ;


  $table = new flexible_table('forumactivitytable');
        $table->course = $course;
    
        
        $table->define_columns(array('id','username', 'totalposts', 'topicposts', 'replies','avgratings','cntratings','maxrating','numrating'));
        $table->define_headers(array('id','User-Name','TotalPosts', 'TopicPosts', 'Replies','AvgRatings','CntRatings','MaxRating','NumRating'));
 
        $table->set_attribute('cellpadding','0');
        $table->set_attribute('class', 'generaltable generalbox reporttable');

         $table->setup();
         
         $users = array();
        
  
        $users = get_records_sql($sql);
        $totalcount=count($users);
        $matchcount=$totalcount;
        
         
        echo '<div id="participationreport">' . "\n";

        $table->initialbars($totalcount > $perpage);
        $table->pagesize($perpage, $matchcount);


       

        echo '
<script type="text/javascript">
//<![CDATA[
function checksubmit(form) {
    var destination = form.formaction.options[form.formaction.selectedIndex].value;
    if (destination == "" || !checkchecked(form)) {
        form.formaction.selectedIndex = 0;
        return false;
    } else {
        return true;
    }
}


//]]>
</script>
';
        echo '<form action="'.$CFG->wwwroot.'/course/report/viewforumactivity.php" method="post" id="forumreport" onsubmit="return checksubmit(this);">'."\n";
        echo '<div>'."\n";
        echo '<input type="hidden" name="id" value="'.$id.'" />'."\n";
        echo '<input type="hidden" name="returnto" value="'. format_string($_SERVER['REQUEST_URI']) .'" />'."\n";
        echo '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />'."\n";

        
        
        $data = array();
        foreach ($users as $u) {
 
         $data=array($u->id,$u->username,$u->totalposts,$u->topicposts,$u->replies,$u->avgratings,$u->numratings,$u->maxrating,$u->numrating);
         $table->add_data($data);
        }

        $table->print_html();
        echo 'Num.Users in the forum:' . count($users);
 
    
        echo '</div>'."\n";
        echo '</form>'."\n";
        echo '</div>'."\n";

    
        
	

print_footer();

?>
