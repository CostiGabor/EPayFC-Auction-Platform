<!DOCTYPE html>
<html>
    <head>  
        <base href="<?= $web_root ?>">
    </head>
    <body>
        <?php Functions::header("Exam", "bi-tags"); ?>
        <div id="full-container" >
            <form>
                <div>
                    <h6>User</h6>
                    <div>
                        <select>
                            <option value="">Choose a user</option>
                            <option value="2" selected>Marc (Marc Michel)</option>
                            <option value="4">Boris (Boris Verhaegen)</option>
                        </select>
                        <button type="submit">OK</button>
                    </div>
                </div>
            </form>

            <div>
                <h6>Category</h6>
                <div>
                    <select>
                        <option value="">Choose a category</option>
                        <option value="3" selected>Electronics</option>
                        <option value="1">Home</option>
                    </select>
                </div>
            </div>

            <div>
                <h6>Items without category: Electronics</h6>
                <div>
                    <input type="checkbox">
                    Bicycle repair kit
                </div>
                <div>
                    <input type="checkbox" disabled>
                    Garden tools set (3 categories)
                </div>
                <button type="button">Add category</button>
            </div>
        </div>
    </body>
</html>
<?php Functions::footer(); ?>