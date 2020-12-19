<!DOCTYPE html>
<html>
<body>
<style>table, td {border: 1px solid black;}</style>

<script>

    /**
    * function sends data to the server and sets the table with using the response.
    */
    function sendData()
    {
      var request = new XMLHttpRequest();
      var brand = document.getElementById("insertBrand").value;
      var price = document.getElementById("insertPrice").value;
      var name = document.getElementById("insertName").value;
      var type = document.getElementById("insertType").value;
      var size = document.getElementById("insertSize").value;

      request.onreadystatechange=function()
      {
        if (request.readyState==4 && request.status==200)
        {
          document.getElementById("table").innerHTML = request.responseText;
        }
      }
      request.open("GET", "Gateway.php?brand="+brand+"&price="+price+"&name="+name+"&type="+type+"&size="+size, true);
      request.send();
    }
</script>

<?php
    /**
    * Variable holds the connection to the database.
    */
    $dbConnection = new mysqli([LOGIN INFORMATION REMOVED]);

    /**
    * Establishes connection to database and creats two tables.
    */
    function connectToDBAndMakeTables() {
        global $dbConnection;
        if ($dbConnection->connect_error) die("Could not connect.");
        $dbConnection->query("DROP TABLE IF EXISTS laptops");
        $dbConnection->query("DROP TABLE IF EXISTS desktops");
        $dbConnection->query("CREATE TABLE laptops (brand VARCHAR(16), price DECIMAL(10, 2), name VARCHAR(16), isTwoInOne BOOL, screenSize DECIMAL(5, 2))");
        $dbConnection->query("CREATE TABLE desktops (brand VARCHAR(16), price DECIMAL(10, 2), name VARCHAR(16), coolingType VARCHAR(16), weight DECIMAL(6, 2))");
    }

    /**
    * Inserts 3 rows into each table.
    */
    function fillTables()
    {
        global $dbConnection;

        $dbConnection->query("INSERT INTO laptops(brand,price,name,isTwoInOne,screenSize) VALUES('lbrand0', 99.99, 'lname0', false, 12.9)");
        $dbConnection->query("INSERT INTO laptops(brand,price,name,isTwoInOne,screenSize) VALUES('lbrand1', 80, 'lname1', false, 16.5)");
        $dbConnection->query("INSERT INTO laptops(brand,price,name,isTwoInOne,screenSize) VALUES('lbrand2', 900, 'lname2', false, 30.0)");

        $dbConnection->query("INSERT INTO desktops(brand,price,name,coolingType,weight) VALUES('dbrand0', 999.99, 'dname0', 'air', 10)");
        $dbConnection->query("INSERT INTO desktops(brand,price,name,coolingType,weight) VALUES('dbrand1', 199.99, 'dname1', 'air', 11)");
        $dbConnection->query("INSERT INTO desktops(brand,price,name,coolingType,weight) VALUES('dbrand2', 298.99, 'dname2', 'air', 12)");
    }

  /**
   * Returns a two-dimensional array containing all of the rows in the database.
   */
    function getRowsFromDB()
    {
        global $dbConnection;
        $arrayToReturn;

        $resultSet = mysqli_query($dbConnection, "SELECT * FROM laptops");

        while($row = mysqli_fetch_array($resultSet))
        {
            $brands[] = $row['brand'];
            $prices[] = $row['price'];
            $names[] = $row['name'];
            $isTwoInOnes[] = $row['isTwoInOne'];
            $screenSizes[] = $row['screenSize'];
        }

        for ($i = 0; $i < sizeof($brands); $i++)
        {
            $arrayToReturn[$i] = array(
                $brands[$i], $prices[$i], $names[$i],
                $isTwoInOnes[$i], $screenSizes[$i]
            );
        }
        //-----------------------
        $resultSet2 = mysqli_query($dbConnection, "SELECT * FROM desktops");

        while($row = mysqli_fetch_array($resultSet2))
        {
            $dbrands[] = $row['brand'];
            $dprices[] = $row['price'];
            $dnames[] = $row['name'];
            $coolingTypes[] = $row['coolingType'];
            $weights[] = $row['weight'];
        }

        for ($i = 0; $i < sizeof($dbrands); $i++)
        {
            $arrayToReturn[$i + sizeof($brands)] = array(
                $dbrands[$i], $dprices[$i], $dnames[$i],
                $coolingTypes[$i], $weights[$i]
            );
        }
        return $arrayToReturn;
    }

    /**
     * Displays an html form as a table of every row in the database.
     */
    function displayFormAsTable($arr)
    {
        // $stringToPrint = "<form action=\"AJAX_Page.php\" method=\"post\">";
        $stringToPrint = <<<_END
            <table style="background-color: #c6ffb3">
                <tr>
                    <td><input type="text" id="insertBrand" size="10" placeholder='brand'></td>
                    <td><input type="text" id="insertPrice" size="10" placeholder='price'></td>
                    <td><input type="text" id="insertName" size="10" placeholder='name'></td>
                    <td><input type="text" id="insertType" size="10" placeholder='type'></td>
                    <td><input type="text" id="insertSize" size="10" placeholder='size'></td>
                </tr>
            </table>
            <button style="background-color: #c6ffb3" onclick="sendData()">Submit</button>
_END;

        $stringToPrint.="<table id=\"table\">";
        for ($i = 0; $i < sizeof($arr); $i++)
        {
          $stringToPrint.="<tr>";

          for ($j = 0; $j < sizeof($arr[$i]); $j++)
          {
            $stringToPrint.="<td>";
            $stringToPrint.=$arr[$i][$j];
            $stringToPrint.="</td>";
          }
          $stringToPrint.="</tr>";
        }
        $stringToPrint.="</table>";
        echo $stringToPrint;
    }

    /**
     * Handles post requests. Makes appropriate opperations
     *  on tables according to data sent by post.
     */
    function handlePost()
    {
        $selectedRadioButton = $_POST['main'];
        if ($selectedRadioButton == "insert")
        {
            if (($_POST['insertBrand'] != "") &&
               ($_POST['insertPrice'] != "") &&
               ($_POST['insertName'] != "") &&
               ($_POST['insertType'] != "") &&
               ($_POST['insertSize'] != ""))
            {
                insertRowIntoDB();
            }
        } else
        {
            $rowNum = (int) substr($selectedRadioButton, 3, 2);
            updateRow($rowNum);
        }
    }

    /**
     * Inserts a new row into the database according
     * to fields in first row of table sent by post.
     */
    function insertRowIntoDB()
    {
        global $dbConnection;

        $brand = $_POST['insertBrand'];
        $price = $_POST['insertPrice'];
        $name = $_POST['insertName'];
        $type = $_POST['insertType'];
        $size = $_POST['insertSize'];

        $sqlStatement;
        if (($type == "0") || ($type == "1")) {
            $sqlStatement = <<<_END1
            INSERT INTO laptops(brand,price,name,isTwoInOne,screenSize)
            VALUES('$brand', $price, '$name', '$type', $size)
_END1;
        } else {
            $sqlStatement = <<<_END2
            INSERT INTO desktops(brand,price,name,coolingType,weight)
            VALUES('$brand', $price, '$name', '$type', $size)
_END2;
        }

        echo $sqlStatement;

        if (mysqli_query($dbConnection, $sqlStatement))
        {
        } else
        {
            echo "Couldn't insert";
        }
    }

    $explanation = <<<_END
    Use the table below to insert a new row into the database (no fields can be left blank).
    It will update automatically using AJAX.
_END;
    echo $explanation;

    connectToDBAndMakeTables();
    fillTables();
    displayFormAsTable(getRowsFromDB());

    highlight_file("./AJAX_Page.php");
    highlight_file("./Gateway.php");
?>

</body>
</html>
