<?php include('header.php');?>

<a class="github-fork-ribbon right-top" href="https://github.com/Cpcong/roc.git" title="Fork me on GitHub">Fork me on GitHub</a>
<div class="cover-container">
<div class="container">
    <div class="row">
        <div class="col-md-12 top_padding">
            <h1><?=$title?></h1>
        </div>
        <div class="col-md-12">
            <p><?=$desc?></p>
        </div>
        <div class="col-md-12 mid_padding">
            <form id="url_form" method="POST">
            <div class="input-group">
                <input type='url' name="long_url" class="form-control" placeholder="scheme://host/path" required>
                <span class="input-group-btn">
                <button id="shorten_btn" class="btn btn-default" type="submit">Shorten</button>
                </span>  
            </div>  
            </form>  
        </div>
        <div class="col-md-12">
            <p id="short_url" class="result_info">a</p>
        </div>
    </div>
</div>
</div>
<?php include('footer.php');?>
