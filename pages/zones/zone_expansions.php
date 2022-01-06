<?php
$print_buffer .= "<div class = 'display_table container_div'>";
	$print_buffer .= "<h2 class='section_header'>Zones</h2>";
	$print_buffer .= "<ol>";
		if (!isset($_GET["expansion"])) {
			$page_title = "Zones By Expansion";
			foreach ($expansion_zones as $expansion => $expansion_value) {
				$print_buffer .= "<li><a href = '?a=zone_expansions&expansion=" . $expansion . "'>" . $expansion_value . "</a></li>";
			}
		} else {
			$expansion = $_GET["expansion"];
			$page_title = $expansion_zones[$expansion];
			$print_buffer .= get_zones_by_expansion($expansion);
		}
	$print_buffer .= "</ol>";
$print_buffer .= "</div>";
?>