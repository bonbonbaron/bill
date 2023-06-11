 <!DOCTYPE html>
 <html>
<head>
<style>
 body {
 font-size: 18px;
 font-family: Arial, sans-serif;
 line-height: 1.6;
 margin: 10px;
 }
 table {
 width: 100%;
 border-collapse: collapse;
 }
 th, td {
 padding: 10px;
 }
 a.row-link {
 display: block;
 text-decoration: none;
 }
 a.row-link:hover {
 background-color: rgba(0, 0, 0, 0.05);
 }
 a.row-link > tr {
 cursor: pointer;
 }
 a.row-link > tr > td {
 padding: 4px;
 }
 tr:nth-child(odd) {
 background-color: #e0e0e0e0;
 }
 .amount {
 font-size: 32px;
 }
 .bill_name {
 font-size: 22px;
 }
</style>
	</head>
 <body>
   <?php
	function shortenAmount($amount) {
	 $units = array('', 'K', 'M','B','T','Qa','Qi');
	 $unitIndex = 0;
	 while ($amount >= 1000) {
	 $amount /= 1000;
	 $unitIndex++;
	 }
	 return round($amount, 1) . $units[$unitIndex];
	}
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);
   ?>
   <h1>Bills</h1>
   <?php
     // Connect to the SQLite3 database
     $db = new SQLite3('/home/bonbonbaron/hack/bill/.db');
     if (!$db) {
	     echo "no database connection";
     }

     // Execute a SELECT query on the mytable table
     $midnight = strtotime("midnight", time());
     #echo "<h1>midnight is $midnight</h1>";
     #$results = $db->query("select sum(a.amt) as amt, b.desc as desc from amt a, bill b where b.id = a.billId and b.lastUpdatedTm > $midnight group by b.id order by amt desc");
     $results = $db->query("
	select ifnull(sum(a.amt), 0.00) as amt, 
		b.desc as desc, 
		b.filepath as filepath, 
		b.name
	from amt a, bill b 
	where b.id = a.billId 
	group by b.id 

	UNION 

	select 0.00 as amt, 
		b.desc, 
		b.filepath as filepath, 
		b.name
	from bill b 
	where b.id not in (select distinct billId from amt) 
	order by amt desc");

     // If there are results, display them in an HTML table
     if ($results) {
       echo "<table border='0'>";
       echo "<tr><th>Total Dollars Mentioned</th><th>Name</th></tr>";
       while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
	 #echo "<h1>{$row['name']}</h1>"
	 #$amt = number_format($row['amt'], 0, ".", ",");
	 $amt = shortenAmount($row['amt']);
         echo "<tr>";
         echo "<td class=\"amount\">\$$amt</td>";
	 if ($row['filepath'] == "") {
           echo "<td class=\"bill_name\">{$row['desc']}</a></td>";
	 }
	 else {
           echo "<td class=\"bill_name\"><a href=\"//192.168.1.72:81/congress/118/bill/{$row['name']}.xml\" class=\"row-link\">{$row['desc']}</a></td>";
	 }
         echo "</tr>";
       }
       echo "</table>";
     } else {
       echo "No results found.";
     }

     // Close the database connection
     $db->close();
   ?>
 </body>
</html>
