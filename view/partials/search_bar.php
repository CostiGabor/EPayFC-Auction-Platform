<?php 
$config = file_exists("config/dev.ini") 
    ? parse_ini_file("config/dev.ini", true) 
    : parse_ini_file("config/prod.ini", true);

if($config["JavaScript"]['disable_js'] == false) { ?>
    <div class="input-group my-4">
        <span class="input-group-text" id="basic-addon1">
            <i class="bi bi-search"></i>
        </span>
        <input type="text" id="search_input" class="form-control"  value="<?= $search ?>"
            placeholder="Search items..." 
            aria-label="Search items..." 
            aria-describedby="basic-addon1">
    </div>

    <select id="search_categories_filter" class="form-select form-control-lg text-secondary bg-dark border-secondary fw-bold" style="width: auto; min-width: 180px;" aria-label="Categories">
        <option value="0" selected>All categories</option>
        <?php foreach (Category::getAll() as $key => $value) {
            $selected ="";
            if($value->getId() == (int) $catSelected){
                $selected="selected";
            }
            echo "<option value='{$value->getId()}' {$selected}>{$value->getName()}</option>";
        } ?>
    </select>
<?php }?>