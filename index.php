<!DOCTYPE html>
<html lang="en">
<head>
    <title>Binary tree</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="binaries.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="binaries.js"></script>
</head>
<body>
<div class="text-center">
    <button class="init-tree btn btn-success m-5">Initialize Binary Tree</button>
    Level <input id="level" class="form-control" type="number" min="2" max="12" value="5">
    <div class="table-responsive">
        <table class="table">
            <tbody></tbody>
        </table>
    </div>
    <input type="submit" class="change-binary-tree btn btn-success" value="Change Binary Tree"/>
    <div class="change-param">
        <label for="from">From <input id="from" class="form-control" type="number" min="2" max="" value="" required></label>
        <label for="to">To <input id="to" class="form-control" type="number" min="2" max="" value="" required></label>
    </div>
</div>
</body>
</html>