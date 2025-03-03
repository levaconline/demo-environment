<h1>Google Search Images API</h1>
<h2>Search Images by: <?php echo $class->data['q']; ?></h2>
<hr>
<div class="row row-cols-1 row-cols-md-3 g-4">

    <?php
    foreach ($class->data['result']['items'] as $key => $value) {
    ?>
        <div class="col">
            <div class="card">
                <a target="_blank" href="<?php echo $value['link']; ?>"><img src="<?php echo $value['image']['thumbnailLink']; ?>" alt="<?php echo $value['title']; ?>"></a>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $value['title']; ?></h5>
                    <p class="card-text">
                        <a target="_blank" href="<?php echo $value['image']['contextLink']; ?>"><?php echo $value['image']['contextLink']; ?></a>
                        <br>
                        <?php echo $value['displayLink']; ?>
                    </p>
                </div>
            </div>
        </div>
    <?php
    }
    ?>
</div>
<hr>
<a href='?c=GoogleSearchImagesApi'>Back</a>
<br>
<br>