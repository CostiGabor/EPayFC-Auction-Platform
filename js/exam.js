$(document).ready(function(){

    usersPart();

    $(`#full-container`).on('change','#userSelect',async function(){
            let userId = $(`#userSelect`).val();
            let responseCat = await $.get(`exam/getCategories/${userId}`);
            let catDiv = $(`<div id="cat-container"></div>`);
            //si cat-container existe deja, on le remplace
            
            let h6 = $(`<h6 class="text-white">Category</h6>`); 
            let select =$(`<select id="catSelect">
                        <option value="">Choose a category</option>
                    </select>`);
            //remplir le select
            responseCat.cats.forEach(element =>{
                let option=$(`<option value="${element.id}" >${element.name}</option>`); 
                select.append(option);
            }); 
            //assemblage
            catDiv.append(h6);
            catDiv.append(select);     
            if($(`#cat-container`).length > 0){
                $(`#cat-container`).replaceWith(catDiv);
            }else{
                $(`#full-container`).append(catDiv);
            }  
           
    });

    $(`#full-container`).on('change','#catSelect', async function () {
        console.log("im in items");
        let catId = $(`#catSelect`).val();
        let userId = $(`#userSelect`).val();

        loadItems(catId,userId);

        //let form = $(`<form action="exam/goGetItems" method="post">
        //                <input type="hidden" name="userId" value="${userId}">
        //                <input type="hidden" name="catId" value="${catId}"> 
        //                </form>`);
        //             $(`#full-container`).append(form);
        //             form.submit();   
        
    });

    async function loadItems(categoryId, uId) {
        let catId = categoryId;
        let userId = uId;
        let responseItems = await $.get(`exam/getItemsNotInCategory/${userId}/${catId}`);
        let categoryName = $('#catSelect option:selected').text();
        let userName = $('#userSelect option:selected').text();
        let itemsDiv = $(`<div id="items-container"></div>`);

        let title=$(`<h6 class="text-white">Items from ${userName} without category:${categoryName}</h6>`);

        itemsDiv.append(title);

        if(responseItems.items.length === 0){
            let textNoItem=$(`<h6 class="text-white">No items without this category.</h6>`);
            $(`#full-container`).append(textNoItem);
        }else{

            
            responseItems.items.forEach(item => {    
           
            let isDisabled = item.nbCat >= 3 ? "disabled" : "";
            let div=$(`<div class="text-white">
                        <input type="checkbox" ${isDisabled} data-id="${item.id}">
                            ${item.title} ${item.nbCat >= 3 ? "(3 categories)" : ""}
                    </div>`);
                    
                   
            itemsDiv.append(div);
            });

            let btn =$(`<button type="button" id="btnAdd" disabled>Add category</button>`);

            itemsDiv.append(btn);
            if($(`#items-container`).length > 0){
                $(`#items-container`).replaceWith(itemsDiv);
            }else{
                $(`#full-container`).append(itemsDiv);
            }
            $(`#full-container`).off('change','input[type=checkbox]').on('change','input[type=checkbox]',function(){
                let itemsCoches = $(`#full-container input[type=checkbox]:checked`).length > 0;
                $(`#btnAdd`).prop('disabled',!itemsCoches);
            });
            $(`#full-container`).on('click','#btnAdd',async function () {
                $dataIds = $(`#full-container input[type=checkbox]:checked:not(:disabled)`);
                $itemsIds = [];
                $dataIds.each(function(){
                    $itemsIds.push($(this).data('id'));
                });
                let responseRegister = await $.post(`exam/registerItemToCat`,{"itemList":$itemsIds,"catId":catId});
                loadItems(categoryId,uId);
            });
        }    
    }

    


    async function usersPart() {
        let fullContainer = $(`#full-container`);

        fullContainer.html(``);
        let responseUsers = await $.get(`exam/getUsers`);
        let userDiv = $(`<div></div>`);
        let h6 = $(`<h6 class="text-white">User</h6>`);  
        let select = $(`<select id="userSelect">
                            <option value="">Choose a user</option>
                        </select>`);           
        //remplir le select
        responseUsers.usersList.forEach(element => {
            let option=$(`<option value="${element.id}" >${element.pseudo} (${element.fullName})</option>`);
            select.append(option);
        });
        //assemblage
        userDiv.append(h6);
        userDiv.append(select);
        fullContainer.append(userDiv);
    };

    

            
    
});