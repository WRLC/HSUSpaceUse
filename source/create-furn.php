<?php
    session_start();
	//Get's username from the login and greets the user to the homepage
	//Also add instructions for new users to help navigate the page.
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title> SpaceUse Furniture/Activity Upload </title>
    <meta charset="utf-8" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <link rel="stylesheet" href="styles/layout.css" type="text/css" >
    <link rel="stylesheet" href="styles/format.css" type="text/css" >
</head>
<body>
    <header>
        <img class="logo" src="images/hsu-wm.svg">
        <h1>SpaceUse</h1>

    <?php
        if (array_key_exists("username", $_SESSION)){
            ?>
            <h3 class="log-state"> Logged In: <?= $_SESSION["username"]?> </h3>
            <?php
        }
    ?>

        <?php
            if (!array_key_exists("username", $_SESSION)){
                ?>
                <p class="invalid-login"> Please first <a href="index.php">login</a> before accessing the app</p>
                <?php
            }
            else{
                 ?>
                <nav>
                    <p class="nav"><a href="home.php">Home</a></p>
                    <p class="nav"><a href="data-collection.php">Data Collection</a></p>
                    <p class="nav"><a href="query-select.php">Query Report</a></p>
                    <p class="nav"><a href="editor.php">Layout Creator</a></p>
                    <p class="nav selected"><a href="upload-select.php">Upload</a></p>
                    <p class="nav"><a href="logout.php">Logout</a></p>
                </nav>
    </header>
    <main>
        <div id="furn_upload_container">
            <div class="furn_upload_div" >
                <h2>Add a new activity: </h2>
                <form class="uploader_furn" action="upload-act.php" method="post" enctype="multipart/form-data">
                    <div style="margin-top: 10px; margin-bottom: 15px; text-align: left;">
                        <p style="display: inline; margin-right: 21px;">Name of the activity: </p>
                        <input style="display: inline;" type="text" name="actName" id="actName" value="" required/>
                    </div>

                    <div style="text-align: left;">
                        <p style="display: inline; margin-right: 5px;">What is the activity for: </p>
                        <select style="display: inline;" name="wbOrFurn" id="wbOrFurn">
                            <option value="furn">Furniture</option>
                            <option value="wb">Whiteboard</option>
                        </select>
                    </div>
                    <input type="submit" value="Upload Activity" name="submit" id="upload_submit">
                </form>
            </div>
                
            <div class="furn_upload_div">
                <h2>Upload a new furniture image: </h2>
                <form class="uploader_furn" action="upload-furn.php" method="post" enctype="multipart/form-data">
                    
                        <p style="text-align: center; font-size: 30px;"> Furniture Upload Coming Soon! </p>
                
                <!--TODO: implement a upload for furniture pieces, right now all the furn icons are hard coded (icons.js)
                        Switch this to more of a dynamic solution
                    
                    <div class="upload">
                        <p>Select image to upload:</p>
                        <input type="file" name="fileToUpload" id="fileToUpload"/>
                    </div>

                    <div class="upload">
                        <p>Please enter the name of the furniture piece: </p>
                        <input type="text" name="newFurnName" id="imageName" value="" required/>
                    </div>

                    <div class="upload">
                        <p>How many seats are available at the piece of furniture: </p>
                        <input type="number" name="floorNum" id="imageNum" value="1" min="1" required/>
                    </div>
                    <input type="submit" value="Upload Image" name="submit" id="upload_submit"/>-->
                </form>
            </div>
        </div>
    </main>
                <?php
                }
            ?>
    <footer>
        <p>Designed by HSU Library Web App team. &copy; Humboldt State University</p>
    </footer>
</body>
</html>