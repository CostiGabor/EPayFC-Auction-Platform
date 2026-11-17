$(document).ready(function () {

    if($('#search_input').val().trim() !="" || $('#search_categories_filter').val() != "0"){        
        if($("#browse_items").length){
    // Si on a une valeur dans l'input quand on arrive sur la page, on lance la function getItems(); 
            getItems();
            console.log("in browse");
        }if($("#my_own_items_page").length){
            getMyItems();
            
        }    
    }

    async function getItems() {
        // on récupère la valeur de l'input de recherche qu'on trim
        let search = $('#search_input').val().trim();        
        let cat = $('#search_categories_filter').val();
        try {               
            let response;
            // On récupère les données qu'on stock dans response
        if (search === "") {
            // URL générée : item/getItemsBySearch/4
            response = await $.get('item/getItemsBySearch/' + cat);
        } else {
            // URL générée : item/getItemsBySearch/4/audio
            response = await $.get('item/getItemsBySearch/' + cat + "/" + search);   
        }
            createItems(response.items_im_participating, "#participatingIn .row",response.param_nav_search);
            createItems(response.other_available_items, "#otherItems .row",response.param_nav_search);   

        } catch (error) {
            console.log("ERREUR")
        }
    }

    async function getMyItems(){
        //recup la valeur de l'input de recherche        
        let search = $('#search_input').val().trim();
        let cat = $('#search_categories_filter').val();
        try{
            let response;
            //recup les données qu'on stock dans response
            if (search === "") {
                response = await $.get('item/getMyItemsBySearch/' + cat);
            } else {
                response = await $.get('item/getMyItemsBySearch/' + cat + "/" + search);   
            }
            
            createItems(response.my_active_items, "#activeItems .row",response.param_nav_search);
            createItems(response.my_closed_items, "#closedUnsoldItems .row",response.param_nav_search);
            createItems(response.my_sold_items, "#soldItems .row",response.param_nav_search);
            
        }catch (error){
            console.log(error);
        }
    }


    function addImageItem(card,item){
        if (!item.picture_path || item.picture_path === "") {
            // CAS 1 : pas d'image
            card.find('.has-picture').addClass("d-none"); 
            card.find('.has-no-picture').removeClass("d-none"); 
        } else {
            // CAS 2 : image présente
            card.find('.has-picture').attr('src', item.picture_path).removeClass("d-none");
            card.find('.has-no-picture').addClass("d-none"); 
        }
    }
  
    function addLink(card,item,param_nav_search){
        const linkItem = card.find('.item-link');
        linkItem.attr('href', `item/show_item/${item.id}/1/${param_nav_search}`);
    }

    function addBadgeBidder(card,item,container){
        if(!container.includes('#participatingIn')) {
            card.find('.bidder-badge').addClass("d-none");
            card.find('.highest-bidder-badge').addClass("d-none");
            return;
        }

        if(item.is_highest_bidder){
            card.find('.highest-bidder-badge').removeClass("d-none");
            card.find('.bidder-badge').addClass("d-none");
        }else {
            card.find('.highest-bidder-badge').addClass("d-none");
            card.find('.bidder-badge').removeClass("d-none");
        }
    }

    function addAuctionBuyNow(card,item){
        if(!item.is_auction){
            card.find('.auction-badge').addClass("d-none");
        }
        if(!item.has_buy_now){
            card.find('.buynow-badge').addClass("d-none");
        }
    }

    function addImgCount(card,item){
        card.find('.badge-img-count').append(" "+item.pictures_count +" images" );
    }

    function addInformations(card,item){
        card.find('.item-title').append(item.title);
        card.find('.item-owner').append("by "+item.owner_pseudo);
    }

    function addPrice(card,item){
        let price;
        let text_color;
        if(item.is_auction && !item.has_buy_now) {
            price = item.starting_bid;
            text_color = 'text-warning';
        }else {
            price = item.buy_now_price;
            text_color = 'text-primary';
        }
        card.find('.item-price').append(price).addClass(text_color);
        
        if(item.max_bid != null){
            card.find('.current-bid-value').append(item.max_bid);
        }else if(item.starting_bid > 0){
             card.find('.current-bid-value').append(item.starting_bid);
             card.find('.current-bid-area').children().first().text("Starting bid");
        }else{
            card.find('.current-bid-area').addClass("d-none");
        }
        

    }

    function addTimer(card,item){
   
        let time_left =item.time_left;
        let days_left = parseInt(time_left.split('d')[0]);
        card.find('.time-left').text(" "+item.time_left);
        const timer_span = card.find('.item-timer');
        if(days_left==0)
            timer_span.addClass("text-warning");
        else
            timer_span.addClass("text-success");

    }

    function createItems(items,containerSelector, param_nav_search) {
        const $container = $(containerSelector);
        $container.html(''); // On vide le conteneur spécifique

        if (items && items.length > 0) {
            items.forEach(item => {
                let card = $($("#template-card").html());
                addLink(card, item,param_nav_search);
                addImageItem(card, item);
                addBadgeBidder(card,item,containerSelector);
                addAuctionBuyNow(card, item);
                addImgCount(card, item);
                addInformations(card, item);
                addPrice(card,item);
                addTimer(card,item);
                $container.append(card);
            });
            return;
        }
        $container.append('<p class="text-center text-light mt-3">Il n\'y a aucune annonce correspondate à votre recherche! </p>');
    }
  
    if($("#browse_items").length){
        $("#search_input").on("input", getItems);
        $("#search_categories_filter").on("change", getItems);
    }
    else   {
        $("#search_input").on("input", getMyItems);
        $("#search_categories_filter").on("change", getMyItems);
    }
});
