$(document).ready(function () {  
    $( "#sortable" ).sortable({
        // Création d'une methode async pour faire des appels ajax 
        update: async function(){
            // on récupère l'id de l'image
            let itemId = getImageId() ?? null;
            // création d'un tableau vide pour récupérer les priority au changement(au drag and drop), le tableau est écrasé a chaque drag and drop ce qui évite le cumule
            let orderPriority= [];
            // Methode getImagesOrder qui rempli le tableau            
            getImagesOrder(orderPriority);
            try {// requete a la quelle on ajoute l'id de l'item et le tableau des priority, pour mettre a jours la DB
                await $.post('item/updateImagePriority/', { itemId : itemId,order: orderPriority });
            }
            catch (error) {
                console.log(error);
            }
            // on change les values des buttons dans le code html
            updateButtonAccesAndValue();
        }      
    });
} );


    function getImagesOrder(orderPriority){
        return $("#sortable").find('button[name="addPriority"]').each(function(){
            orderPriority.push($(this).val());
        });
    }

    function getImageId(){
        return $("#sortable").find('img').data('item-id');
    }

    function updateButtonAccesAndValue(){
        const nbImages = $("#sortable").find('img').length;// on récupère le nombre d'image dans la div id="sortable" ce qui nous permet de savoir combien dimage on a.
        
        $('button[name="addPriority"]').each(function(index) {   // on active ou desactive le button selon son emplacement , index commence a 0
          $(this).prop('disabled', index == 0)
          $(this).val(index+1);
        });
        $('button[name="removePriority"]').each(function(index){            
            $(this).prop('disabled',index+1 == nbImages)
            $(this).val(index+1);
        });
        $('#sortable button[name="delete"]').each(function(index){             
            $(this).val(index+1);
        });

    }
   