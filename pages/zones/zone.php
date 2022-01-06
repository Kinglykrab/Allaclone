<?php

$name = (isset($_GET['name']) ? addslashes($_GET['name']) : '');
$order = (isset($_GET['order']) ? addslashes($_GET["order"]) : 'name');
$mode = (isset($_GET['mode']) ? addslashes($_GET["mode"]) : '');
$first_row = (isset($_GET['first_row']) ? addslashes($_GET["first_row"]) : '');

if ($use_custom_zone_list == TRUE && $name != '') {
    $ZoneNote = get_field_result("note", "SELECT note FROM $zones_table WHERE short_name='$name'");
    if (substr_count(strtolower($ZoneNote), "disabled") >= 1) {
        header("Location: index.php");
        exit();
    }
}

$page_title = get_field_result("long_name", "SELECT long_name FROM $zones_table WHERE short_name='$name'") . " ($name)";

if (!isset($name)) {
    $print_buffer .= "<script>document.location=\"zones.php\";</script>";
}

$ZoneDebug = FALSE; // this is new in 0.5.3 but undocumented, it is for world builders

$resources_menu = "<h2 class='section_header'>Resources</h2>";
$resources_menu .= "<ul>";
$resources_menu .= "<li><a href = '?a=zone&name=$name&mode=fishing'>" . $zone["long_name"] . " Fishing</a>";
$resources_menu .= "<li><a href = '?a=zone&name=$name&mode=forage'>" . $zone["long_name"] . " Forage</a>";
$resources_menu .= "<li><a href = '?a=zone&name=$name&mode=npcs&first_row=0'>" . $zone["long_name"] . " NPCs</a>";
if ($display_task_info == TRUE) {
    $resources_menu .= "<li><a href = '?a=zone&name=$name&mode=tasks'>" . $zone["long_name"] . " Tasks</a>";
}
$resources_menu .= '</ul';


$print_buffer .= '<table class="display_table container_div"><tr><td>';

$print_buffer .= $resources_menu;


$query = "
    SELECT
        $zones_table.*
    FROM
        $zones_table
    WHERE
        $zones_table.short_name = '$name'
";
$result = db_mysql_query($query) or message_die('zones.php', 'MYSQL_QUERY', $query, mysqli_error());
$zone = mysqli_fetch_array($result);
$print_buffer .= "<table style='width:100%'><tr valign=top><td>";
if ($mode == "npcs") {
    ////////////// NPCS
    $query = "SELECT $npc_types_table.id,$npc_types_table.class,$npc_types_table.level,$npc_types_table.trackable,$npc_types_table.maxlevel,$npc_types_table.race,$npc_types_table.`name`,$npc_types_table.maxlevel,$npc_types_table.loottable_id
		FROM $npc_types_table,$spawn2_table,$spawn_entry_table,$spawn_group_table";
    $query .= " WHERE $spawn2_table.zone='$name'
		AND $spawn_entry_table.spawngroupID=$spawn2_table.spawngroupID
		AND $spawn_entry_table.npcID=$npc_types_table.id
		AND $spawn_group_table.id=$spawn_entry_table.spawngroupID";

    if ($hide_invisible_men == TRUE) {
        $query .= " AND $npc_types_table.race!=127 AND $npc_types_table.race!=240";
    }
    if ($group_npcs_by_name == TRUE) {
        $query .= " GROUP BY $npc_types_table.`name`";
    } else {
        $query .= " GROUP BY $npc_types_table.id";
    }
    $query .= " ORDER BY $order";
    $result = db_mysql_query($query) or message_die('zone.php', 'MYSQL_QUERY', $query, mysqli_error());
    $print_buffer .= "<h2 class = 'section_header'>NPCs</h2>";
	$count = 0;
    if (mysqli_num_rows($result) > 0) {
		$num_rows = mysqli_num_rows($result);
		$previous_start = ($first_row - $max_npcs_returned);
		$last_row = ($first_row + $max_npcs_returned);
		$print_buffer .= ($num_rows > 1 ? "<ol start = '" . ($first_row + 1) . "'>" : "<ul>");
        while ($row = mysqli_fetch_assoc($result)) {
			if ($count == $first_row && $first_row >= $max_npcs_returned) {
				$print_buffer .= "<a href = '?a=zone&name=$name&mode=npcs&first_row=$previous_start'>Previous $max_npcs_returned NPCs</a>";
			}
			
			if ($count >= $first_row && $count < $last_row) {
				if ((get_npc_name_human_readable($row["name"])) != '' && ($row["trackable"] > 0 || $trackable_npcs_only == FALSE)) {
					$print_buffer .= "<li><a href = '?a=npc&id=" . $row["id"] . "'>" . get_npc_name_human_readable($row["name"]) . "</a></li>";
				}
			}
			
			if ($count == $last_row) {
				$print_buffer .= "<a href = '?a=zone&name=$name&mode=npcs&first_row=$last_row'>Next $max_npcs_returned NPCs</a>";
				return;
			}
			$count++;
        }
        $print_buffer .= ($num_rows > 1 ? "</ol>" : "</ul>");
    } else {
        $print_buffer .= "<ul><li>No entries found.</li></ul>";
    }
} // end npcs

if ($mode == "forage") {
    $query = "
        SELECT
            $items_table.`name`,
            $items_table.`id`
        FROM
            $items_table,
            $forage_table,
            $zones_table
        WHERE
            $items_table.`id` = $forage_table.`itemid`
        AND $forage_table.`zoneid` = $zones_table.`zoneidnumber`
        AND $zones_table.`short_name` = '$name'
        ORDER BY
            $items_table.`name` ASC
    ";
    $result = db_mysql_query($query) or message_die('zone.php', 'MYSQL_QUERY', $query, mysqli_error());
       $print_buffer .= "<h2 class = 'section_header'>Forage</h2>";
    if (mysqli_num_rows($result) > 0) {
		$num_rows = mysqli_num_rows($result);
		$print_buffer .= ($num_rows > 1 ? "<ol>" : "<ul>");
        while ($row = mysqli_fetch_assoc($result)) {
			$print_buffer .= "<li><a href = '?a=item&id=" . $row["id"] . "'>" . $row["name"] . "</a></li>";
        }
       $print_buffer .= ($num_rows > 1 ? "</ol>" : "</ul>");
    } else {
        $print_buffer .= "<ul><li>No entries found.</li></ul>";
    }
}

if ($mode == "fishing") {
    $query = "
        SELECT
            $items_table.`name`,
            $items_table.`id`
        FROM
            $items_table,
            $fishing_table,
            $zones_table
        WHERE
            $items_table.`id` = $fishing_table.`itemid`
        AND $fishing_table.`zoneid` = $zones_table.`zoneidnumber`
        AND $zones_table.`short_name` = '$name'
        ORDER BY
            $items_table.`name` ASC
    ";
    $result = db_mysql_query($query) or message_die('zone.php', 'MYSQL_QUERY', $query, mysqli_error());
       $print_buffer .= "<h2 class = 'section_header'>Fishing</h2>";
    if (mysqli_num_rows($result) > 0) {
		$num_rows = mysqli_num_rows($result);
		$print_buffer .= ($num_rows > 1 ? "<ol>" : "<ul>");
        while ($row = mysqli_fetch_assoc($result)) {
			$print_buffer .= "<li><a href = '?a=item&id=" . $row["id"] . "'>" . $row["name"] . "</a></li>";
        }
       $print_buffer .= ($num_rows > 1 ? "</ol>" : "</ul>");
    } else {
        $print_buffer .= "<ul><li>No entries found.</li></ul>";
    }
}

if ($mode == "tasks") {
    if ($display_task_info == TRUE) {
        $ZoneID = get_field_result("zoneidnumber", "SELECT zoneidnumber FROM zone WHERE short_name = '$name'");
        $query = "
            SELECT
                DISTINCT t.id,
				t.type,
				t.duration,
				t.duration_code,
                t.title,
				t.description,
                t.reward,
                t.rewardid,
				t.cashreward,
				t.xpreward,
                t.rewardmethod,
                t.minlevel,
                t.maxlevel,
				t.repeatable,
				t.faction_reward,
				t.completion_emote,
				ta.zones
            FROM
                $task_table t INNER JOIN $task_activities_table ta ON t.id = ta.taskid
             WHERE
                ta.zones LIKE '%$ZoneID%'
            ORDER BY
                t.id ASC
        ";
        $result = db_mysql_query($query) or message_die('zone.php', 'MYSQL_QUERY', $query, mysqli_error());

		$print_buffer .= "<h2 class='section_header'>Tasks</h2>";
        if (mysqli_num_rows($result) > 0) {
			$print_buffer .= "<ol>";
            $RowClass = "lr";
            while ($row = mysqli_fetch_array($result)) {
                $Reward = $row["reward"];
                if ($row["rewardmethod"] == 0) {
                    if ($row["rewardid"] > 0) {
                        $ItemID = $row["rewardid"];
                        $ItemName = get_field_result("Name", "SELECT Name FROM items WHERE id = $ItemID");
                        $Reward = "<a href = '?a=item&id=" . $ItemID . "'>" . $ItemName . "</a>";
                    }
                }

                $print_buffer .= "<li><a href = '?a=task&id=" . $row["id"] . "'>" . $row["title"] . "</a></li>";
            }
			$print_buffer .= "</ol>";
        } else {
            $print_buffer .= "<ul><li>No entries found.</li></ul>";
        }
    }

} // end Tasks

$print_buffer .= "</td><td>"; // end first column
$print_buffer .= "</td></tr>";
$print_buffer .= "</table>";

$print_buffer .= '</td></tr></table>';

?>