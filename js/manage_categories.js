$(document).ready(function() {
    let $container = $('#manage_categories_container');

     $container.html(`
        <ul id="sortable" class="list-unstyled d-flex flex-column gap-2 m-0 p-0 text-white mb-4">
           
        </ul>

        <div id="flash-message" class="alert alert-success d-none my-2 small py-2" role="alert">
            Catégorie ajoutée avec succès !
        </div>
        <div class="d-flex align-items-center gap-2">
            <input type="text" id="input_addCategory" class="form-control form-control-sm bg-dark text-light border-secondary d-none flex-grow-1">
            
            <button id="btn_addCategory" type="submit" class="btn btn-primary btn-sm ms-auto">
                <i class="bi bi-plus"></i>
            </button>
        </div>
        <ul id="errors_add_category" class="errors-list text-danger fs-small d-none m-0 p-0 mt-1 ps-5" style="list-style-type: none;"></ul>
    `);
    
    $("#sortable" ).sortable({
        update: async function(event,ui){
            let upd;
            try {
                let catID = parseInt(ui.item.attr('data-id'));
                let newPriority = ui.item.index() + 1;
                if(ui.item.index()+1 >=ui.item.attr('data-priority')){    
                    upd = await $.post('category/swapCategories' , {"direction":"down","newPriority":newPriority,"catID":catID});  
                }else{                    
                    upd = await $.post('category/swapCategories' , {"direction":"up","newPriority":newPriority,"catID":catID});                 
                }
            await getCategories();
            } catch (error) {
                console.log(error);
            }          
        }
    });

    
    async function getCategories() {    
        let response;
        try {
            response = await $.get('category/getCategories/');      
        } catch (error) {        
            console.log("ERREUR")
        }
        addCategories(response);
    }
    $("#btn_addCategory").on("click", function(){
        let inputAddCategory = $("#input_addCategory");
        inputAddCategory.toggleClass("d-none");
        inputAddCategory.on('focusout', function(){
            if (inputAddCategory.val().trim() !== "") {
                addNewCategory($(this));
            }
        });
        
    })
    function addCategories(categories){
        const list = $('#sortable');
        list.html("");
        categories.forEach(cat => {
        let isDisabled = cat.itemCount > 0 ? "disabled" : "";
        let li = 
        `<li class="ui-state-default d-flex flex-column rounded w-100 px-3 py-2" data-id="${cat.id}" data-priority='${cat.priority}'>            
            
            <div class="d-flex align-items-center gap-3 w-100">
                <span class="text-secondary fs-small" style="user-select: none;">⋮⋮</span>  
                <span class="span_name fs-small fw-semibold text-white-50">${cat.name} (${cat.itemCount})</span>            

                <input type="text" class="input_name form-control form-control-sm bg-dark text-light border-secondary d-none flex-grow-1" data-id="${cat.id}" value="${cat.name}">
                
                <button class="edit btn btn-outline-secondary btn-sm px-3 ms-auto" type="button">Edit</button>            
                <button type="button" class="delete btn btn-outline-danger btn-sm px-3" ${isDisabled}>Delete</button>
            </div>

            <ul id="errors_${cat.id}" class="errors-list text-danger fs-small d-none m-0 p-0 mt-1 ps-5" style="list-style-type: none;"></ul>

        </li>` ;
        li = $(li);
        li.find('.edit').on('click', function(e) {
            li.find('.span_name').toggleClass("d-none" );
            li.find('.input_name').toggleClass("d-none" );
        } );
        li.find('.input_name').on('input', function() {
            editCategory($(this));
        });
        list.append(li);
        const limitModal = bootstrap.Modal.getOrCreateInstance('#deleteModal');
        li.find('.delete').on('click', function(e){  
            $cat = $(this);
            limitModal.show();  
            $('#modal-cat-name').html(cat.name);
            $('#confirm-delete-btn').off('click').on('click', async function() {
                // 1. On lance la suppression
                await deleteCategory($cat);
                // 2. On masque la modale une fois fini
                limitModal.hide();
            });
         });
        });    
    }
    async function editCategory($category){   
        $catName = $category.val().trim();
        $catID =$category.attr('data-id');        
        let ulErrors=$(`#errors_${$catID}`);
        let response;
        try {
            response = await $.post('category/editCategory', {"category_update_name":$catName,"catID":$catID});             
        } catch (error) {        
            console.log("ERREUR")
        }
        if(!response.success){   
            ulErrors.removeClass("d-none");
            ulErrors.html("");
            let lis = "";
            response.errors.category_update.forEach(error => {
                lis += `<li class="">${error}</li>`;
            });        
            ulErrors.html(lis);
        }else{
            $category.prev().text($catName);
            ulErrors.html("");
            ulErrors.add("d-none");
        }
    }

    async function deleteCategory($category){
        let $catID = $category.closest('li').attr('data-id');
        let response;
        try {
            response = await $.post('category/deleteCategory', {"catID":$catID});  
            console.log(response);        
            await getCategories();   
        } catch (error) {        
            console.log("ERREUR")
        }
    }

    async function addNewCategory($newCat){
        $catName = $newCat.val().trim();
        let ulErrors= $("#errors_add_category");
        try {
            response = await $.post('category/addCategory', {"category_name":$catName});          
        } catch (error) {        
            console.log("ERREUR")
        }
         if(!response.success){   
            ulErrors.removeClass("d-none");
            ulErrors.html("");
            let lis = "";
            response.errors.category_name.forEach(error => {
                lis += `<li class="">${error}</li>`;
            });        
            ulErrors.html(lis);
        }else{
            await getCategories();
            ulErrors.html("");
            ulErrors.add("d-none");
            $newCat.val("");
            showTemporaryMessage() ;
        }
    }
    function showTemporaryMessage() {
        let $flash = $('#flash-message');
        $flash.removeClass('d-none');

        setTimeout(function() {
            $flash.addClass('d-none');
        }, 6000);
    }

    getCategories();

  
});