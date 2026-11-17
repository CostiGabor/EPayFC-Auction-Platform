$(document).ready(function () {    
    getImages();  
} );

async function getImages(){
    let container = $(".carousel-inner");
    let containerThumbnail = $(".thumbnail-container");
    let itemId=getImageId();
    let response;
    try {
        response = await $.get('item/getCarouselImages/' +itemId);
        console.log(response);
        }
    catch (error) {
        console.log(error);
    }
    addImage(response,container);
    addThumbnail(response,containerThumbnail);
    switchCarouselImage(container,containerThumbnail);
}


function getImageId(){
    return $("#carouselItem").data('item-id');
}

function addImage(images,container){
    let htmlContent="";
    images.forEach((img,index)=> {
        let active = (index === 0) ? 'active' : '';
        htmlContent += `
            <div class="carousel-item ${active}" data-img-index="${index}">
                <img src="${img}" class="d-block w-100" alt="Image" >
            </div>`;
    });    
    if (images.length <= 1) {
        $('#images-additional').hide();
        $('.carousel-control-prev, .carousel-control-next').hide();
        htmlContent += `
             <div class="w-100 d-flex align-items-center justify-content-center bg-secondary bg-opacity-25"
                style="height:420px;">
                <i class="bi bi-image" style="font-size: 4rem;"></i>
            </div>`;
    }else {
        $('.carousel-control-prev, .carousel-control-next').show();
    }
    container.html(htmlContent);
}

function addThumbnail(images,container){
    let htmlContent="";

    images.forEach((img,index)=> {        
        let borderClass=(index===0) ? "border border-info" : "";
        let dotIndex =img.lastIndexOf('.');       
        let imgThumbnail = img.slice(0,dotIndex) + "_thumbnail" + img.slice(dotIndex);
        htmlContent += `<img class="img-open-item ${borderClass}" src="${imgThumbnail}" alt="" data-img-index="${index}">`;
    });
    container.html(htmlContent);
}

function switchCarouselImage(container,containerThumbnail){    
    let carousel = document.getElementById('carouselItem');
    let carouselInstance = bootstrap.Carousel.getInstance(carousel);

    containerThumbnail.on('click', 'img', function() {
    let imgIndex = $(this).data("img-index");
    // container.find('.active').removeClass('active');
    // let imgP=container.find(`.carousel-item[data-img-index="${imgIndex}"]`);
    // imgP.addClass('active');
    if (!carouselInstance) {
            carouselInstance = new bootstrap.Carousel(carousel);
    }
    carouselInstance.to(imgIndex);
    addBorderInfoToThumbnail($(this), containerThumbnail);
   })
}

function addBorderInfoToThumbnail(clickedImg, containerThumbnail) {
    // On retire la bordure de toutes les miniatures
    containerThumbnail.find('img').removeClass('border border-info');
    // On l'ajoute à celle cliquée
    clickedImg.addClass('border border-info');
}