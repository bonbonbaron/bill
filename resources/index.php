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
       display: block;
       text-decoration: none;
       font-size: 22px;
     }
    </style>
	</head>
 <body>
   <?php
     ini_set('display_errors', 1);
     ini_set('display_startup_errors', 1);
     error_reporting(E_ALL);
   ?>
   <?php
     // Connect to the SQLite3 database
     $db = new SQLite3('/home/bonbonbaron/hack/bill/.db');
     if (!$db) {
	     echo "no database connection";
     }

     // Initialize a PHP array of SQL query results that we'll later convert to a Javascript array.
     // The resulting Javascript array will be more quickly and securely sortable user-side.
     $phpQueryArray = array();
     // Execute a SELECT query on the mytable table
     $midnight = strtotime("midnight", time());
     #echo "<h1>midnight is $midnight</h1>";
     #$results = $db->query("select sum(a.amt) as amt, b.desc as desc from amt a, bill b where b.id = a.billId and b.lastUpdatedTm > $midnight group by b.id order by amt desc");
     $results = $db->query("
        select ifnull(sum(a.amt), 0.00) as amt, 
          b.desc as desc, 
          b.filepath as filepath, 
          b.lastActionDt as lastActionTm,
          b.name,
          b.congress as congress,
          b.file_ext as fileExt
        from amt a, bill b 
        where b.id = a.billId 
        group by b.id 

        UNION 

        select 0.00 as amt, 
          b.desc, 
          b.filepath as filepath, 
          b.lastActionDt as lastActionTm,
          b.name,
          b.congress as congress,
          b.file_ext as fileExt
        from bill b 
        where b.id not in (select distinct billId from amt) 
        order by amt desc limit 50");

     // If there are results, display them in an HTML table
     if ($results) {
       while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
         $phpQueryArray[] = $row;  // append to the PHP-based array
       }
       echo "<table border='0'>";
       echo "<tr><th id=\"hdrAmt\" >Total Dollars Mentioned</th><th id=\"hdrLastUpdate\">Last Update</th><th id=\"hdrNameDate\">Name</th></tr>";
       echo "<tbody id=\"table-body\">";
       echo "</tbody>";
       echo "</table>";
     } else {
       echo "No results found.";
     }

     // Close the database connection
     $db->close();
     // Convert PHP array to a Javascript array that'll be easily sortable.
     // First we'll do this by converting to JSON:
     $phpArrayToJson = json_encode($phpQueryArray);
  ?>
   <script>
     var jsonString = '<?php echo addslashes($phpArrayToJson); ?>';
     // Then we'll turn the JSON string into a Javascript array.
     var jsQueryArray = JSON.parse(jsonString);
     // A function to sort the data array
     function sortArray(data, key, ascending) {
       return data.sort((a, b) => {
         numA = Number.parseInt(a[key]);
         numB = Number.parseInt(b[key]);
         if (numA < numB) return ascending ? 1 : -1;
         if (numB < numA) return ascending ? -1 : 1;
         return 0;
       });
     }

     function secondsToFormattedDate(seconds) {
       // Convert seconds to milliseconds and create a new Date object
       const date = new Date(seconds);

       // Extract and format the required components
       const month = ("" + (date.getMonth() + 1)).slice(-2);
       const day = ("" + date.getDate()).slice(-2);
       const hours = ("" + date.getHours()).slice(-2);
       const minutes = ("0" + date.getMinutes()).slice(-2);

       // Construct the formatted date string
       const formattedDate = `${month}/${day} ${hours}:${minutes}`;

       return formattedDate;
    }


     function shortenAmount(amount) {
       const units = ['', 'K', 'M', 'B', 'T', 'Qa', 'Qi'];
       let unitIndex = 0;
       while (amount >= 1000) {
         amount /= 1000;
         unitIndex++;
       }
       return (Math.round(amount)) + units[unitIndex];
     }

     // A function to render the sorted data into the table
     function renderData(sortedArray) {
       const tableBody = document.getElementById("table-body");
       tableBody.innerHTML = "";

       sortedArray.forEach(row => {
         const tr = document.createElement("tr");
         const tdAmt = document.createElement("td");
         const tdNmDt = document.createElement("td");
         const tdUpdt = document.createElement("td");
         const link = document.createElement("a");
         tdAmt.className = "amount";
         tdNmDt.className = "bill_name";
         tdUpdt.textContent = secondsToFormattedDate(row.lastActionTm);
         tdAmt.textContent = "$" + shortenAmount(row.amt);
         if (row.filepath == "") {
           tdNmDt.textContent = row.desc;
         } else {
           link.href = "./congress/" + row.congress + "/bill/" + row.name + "." + row.fileExt;
           link.textContent = row.desc;
           tdNmDt.appendChild(link);
         }
         tr.appendChild(tdAmt);
         tr.appendChild(tdUpdt);
         tr.appendChild(tdNmDt);
         tableBody.appendChild(tr);
       });
     }

     // Add event listeners to the table headers
     let hdrAmtIsAscending = true;
     let hdrNmDtIsAscending = true;

     // Make amounts column sortable
     document.getElementById("hdrAmt").addEventListener("click", () => {
       sortedArray = sortArray(jsQueryArray, "amt", hdrAmtIsAscending); 
       renderData(sortedArray);
       hdrAmtIsAscending = !hdrAmtIsAscending;
     });

     // Make name column sortable
     document.getElementById("hdrLastUpdate").addEventListener("click", () => {
       sortedArray = sortArray(jsQueryArray, "lastActionTm", hdrNmDtIsAscending); 
       renderData(sortedArray);
       hdrNmDtIsAscending = !hdrNmDtIsAscending;
     });

     // Initial render of jsQueryArray
     renderData(jsQueryArray);
   </script>
  </body>
</html>
