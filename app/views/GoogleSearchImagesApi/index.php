<div class="container mt-5">
    <div class="row d-flex justify-content-center">
        <div class="col-md-6">
            <form action="/?c=GoogleSearchImagesApi&a=getImages" method="post">
                <div class="form-group">
                    <label for="q">Enter word for search</label><br>
                    <input type="text" placeholder="E.G. Marco Polo" name="q" class="form-control-file" id="q" value="<?php echo $class->data['q'] ?? ''; ?>" required>
                </div>
                <input type="hidden" name="token" value="<?php echo $class->data['token'] ?? 'ccc'; ?>">
                <br>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>
</div>