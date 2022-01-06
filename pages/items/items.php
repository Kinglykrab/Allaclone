<?php
/** If the parameter 'isearch' is set, queries for the items matching the criterias and displays them, along with an item search form.
 *    If only one and only one item is found then this item is displayed.
 *  If 'isearch' is not set, displays a search item form.
 *  If no criteria is set then it is equivalent to searching for all items.
 *  For compatbility with Wikis and multi-word searches, underscores are treated as jokers in 'iname'.
 */

$isearch = (isset($_GET['isearch']) ? $_GET['isearch'] : '');
$iname = (isset($_GET['iname']) ? $_GET['iname'] : '');

if (count($_GET) > 2) {
    $query = "SELECT id, icon, `name`, itemtype, ac, hp, mana, damage, delay FROM $items_table WHERE ";    
    if ($iname != "") {
        $name = addslashes(str_replace("_", "%", str_replace(" ", "%", $iname)));
        $query .= " `name` LIKE '%" . $name . "%'";
    }
    $query .= " ORDER BY `name` LIMIT $max_items_returned";
    $QueryResult = db_mysql_query($query);
} else {
    $iname = "";
}

$page_title = "Item Search";

$print_buffer .= '<table><tr><td>';

$print_buffer .= file_get_contents('pages/items/item_search_form.html');

if(!isset($_GET['v_ajax'])){
    $footer_javascript .= '
        <script src="pages/items/items.js"></script>
    ';
}

// Print the query results if any
if (isset($QueryResult)) {
    $num_rows = mysqli_num_rows($QueryResult);
    $total_row_count = $num_rows;
    if ($num_rows > $max_items_returned) {
        $num_rows = $max_items_returned;
    }
    $print_buffer .= "";
    if ($num_rows == 0) {
        $print_buffer .= "<b>No items found...</b><br>";
    } else {
        $OutOf = "";
        if ($total_row_count > $max_items_returned) {
            $OutOf = " (Searches are limited to $max_items_returned Max Results)";
        }
        $print_buffer .= "<br>";

        $print_buffer .= "<table class='display_table container_div datatable' id='item_search_results' style='width:100%'>";
        $print_buffer .= "
            <thead>
                <th class='menuh'>ID</th>
                <th class='menuh'>Icon</th>
                <th class='menuh'>Name</th>
                <th class='menuh'>Type</th>
                <th class='menuh'>Armor Class</th>
                <th class='menuh'>Health</th>
                <th class='menuh'>Mana</th>
                <th class='menuh'>Damage</th>
                <th class='menuh'>Delay</th>
            </thead>
        ";
        $RowClass = "lr";
        for ($count = 1; $count <= $num_rows; $count++) {
            $table_data = "";
            $row = mysqli_fetch_array($QueryResult);
            $table_data .= "<tr valign='top' class='" . $RowClass . "'>";
            $table_data .= "<td>" . $row["id"] . "</td>";
            $table_data .= "<td>";
            if (file_exists($icons_dir . "item_" . $row["icon"] . ".png")) {
                $table_data .= "<img src='" . $icons_url . "item_" . $row["icon"] . ".png' align='left'/>";
            } else {
                $table_data .= "<img src='" . $icons_url . "item_.gif' align='left'/>";
            }
            $table_data .= "</td>";
            $table_data .= "<td><a href='?a=item&id=" . $row["id"] . "' id='" . $row["id"] . "'>" . $row["name"] . "</a></td>";
            $table_data .= "<td>" . $dbitypes[$row["itemtype"]] . "</td>";
            $table_data .= "<td>" . $row["ac"] . "</td>";
            $table_data .= "<td>" . $row["hp"] . "</td>";
            $table_data .= "<td>" . $row["mana"] . "</td>";
            $table_data .= "<td>" . $row["damage"] . "</td>";
            $table_data .= "<td>" . $row["delay"] . "</td>";
            $table_data .= "</tr>";

            if ($RowClass == "lr") {
                $RowClass = "dr";
            } else {
                $RowClass = "lr";
            }

            $print_buffer .= $table_data;
        }
        $print_buffer .= "</table>";
    }
}

$print_buffer .= '</td></tr></table>';


?>
