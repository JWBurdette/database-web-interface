<?php

//The database connection
$dbConnection = new mysqli([LOGIN INFORMATION REMOVED]);

//Variables that are set using GET request data.
$brand = $_GET['brand'];
$price = $_GET['price'];
$name = $_GET['name'];
$type = $_GET['type'];
$size = $_GET['size'];

/**
* Inserts a new row into the database using the values sent by the GET request.
*/
function insertRowIntoDB()
{
    global $dbConnection;
    global $brand;
    global $price;
    global $name;
    global $type;
    global $size;

    $sqlStatement;
    if (($type == "0") || ($type == "1"))
    {
      $sqlStatement = "INSERT INTO laptops(brand,price,name,isTwoInOne,screenSize) VALUES('$brand', $price, '$name', '$type', $size)";
    } else
    {
      $sqlStatement = "INSERT INTO desktops(brand,price,name,coolingType,weight) VALUES('$brand', $price, '$name', '$type', $size)";
    }
    mysqli_query($dbConnection, $sqlStatement);
}

/**
* gets all rows from both tables of the database and returns it as a single 2D array
*/
function getDBContentsAsArray() {
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
function printArrayAsTable($arr)
{
    $stringToPrint = "<table id=\"table\">";
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

insertRowIntoDB();
printArrayAsTable(getDBContentsAsArray());
?>
