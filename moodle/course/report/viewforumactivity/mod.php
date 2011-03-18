<?php // $Id: mod.php,v 1.7.2.3 2008/11/29 14:31:00 skodak Exp $

    if (!defined('MOODLE_INTERNAL')) {
        die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
    }

    if (has_capability('coursereport/viewforumactivity:view', $context)) {
        echo '<p>';
        $viewforumactivity = get_string('viewforumactivity','coursereport_viewforumactivity');
        echo "<a href=\"{$CFG->wwwroot}/course/report/viewforumactivity/index.php?id={$course->id}\">";
        echo "$viewforumactivity</a>\n";
        echo '</p>';
    }
?>
