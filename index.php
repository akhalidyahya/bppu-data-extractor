<?php
// index.php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BPPU Data Extractor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 700px;
            margin: 40px auto;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
        }

        form {
            margin-top: 20px;
        }

        input[type="file"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-top: 10px;
        }

        input[type="submit"] {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .note {
            margin-top: 30px;
            font-size: 0.9em;
            color: #666;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <h1>BPPU Data Extractor</h1>

    <p>Please upload a <strong>ZIP file</strong> containing your PDF documents. The system will process and extract relevant tax data into an Excel file.</p>

    <form action="process.php" method="post" enctype="multipart/form-data">
        <label for="zip_file">Choose ZIP file:</label><br>
        <input type="file" name="zip_file" id="zip_file" accept=".zip" required><br>
        <input type="submit" value="Upload and Process">
    </form>

    <div class="note">
        <p>⚠️ <strong>Privacy Notice:</strong> Your files will be processed temporarily in memory and <strong>not saved</strong> on the server. All temporary data will be deleted immediately after processing.</p>
        <p>🔗 Full source code is available on GitHub: <a href="https://github.com/akhalidyahya/bppu-data-extractor" target="_blank">github.com/akhalidyahya/bppu-data-extractor</a></p>
    </div>

</body>
</html>
