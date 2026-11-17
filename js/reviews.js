$(document).ready(function(){
    let itemId = $('#item-id').val();

    async function getReviews() {
        try {
            let response = await $.get('review/getReviews/' + itemId);
            displayReviews(response);
        } catch(error) {
            console.log(error);
        }
    }

    function displayReviews(reviews) {
        let $list = $('#reviews-list');
        let connectedUser = parseInt($('#connected-user-id').val());
        let isAdmin = $('#is-admin').val() === '1';

        $list.html('');

        if(reviews.length === 0) {
            $list.append('<p class="text-white-50">No reviews yet.</p>');
            return;
        }

       reviews.forEach(review => {
            let deleteBtn = '';
            if(connectedUser === review.author || isAdmin) {
                deleteBtn = `<button class="btn btn-link link-danger btn-sm p-0 delete-btn" data-id="${review.id}">Delete</button>`;
            }

            let $div = $(`
                <div class="border-bottom border-secondary pb-2 mb-2">
                    <div class="d-flex justify-content-between">
                        <span class="text-light fw-bold">⭐ ${review.rating}/5</span>
                        <small class="text-white-50">${review.created_at}</small>
                    </div>
                    <p class="text-white-50 mb-1">${review.comment ?? ''}</p>
                    ${deleteBtn}
                </div>`);
            
            $list.append($div);

            $div.find('.delete-btn').on('click', async function() {
                try {
                    let response = await $.post('review/destroyAjax', {
                        "review_destroy": review.id
                    });
                    if(response.success) {
                        await getReviews();
                    }
                } catch(error) {
                    console.log(error);
                }
            });
        });
        //cacher/montrer le formulaire
        let alreadyReviewed = reviews.some(review => review.author === connectedUser);
        if(alreadyReviewed){
            $('#review-form').addClass('d-none');
        }else{
            $('#review-form').removeClass('d-none');
        }
    }

    // Listener formulaire — EN DEHORS de displayReviews !
    $('#review-form').on('submit', async function(e) {
        e.preventDefault();
        let rating = $('select[name="rating"]').val();
        let comment = $('textarea[name="comment"]').val();

        try {
            let response = await $.post('review/storeAjax', {
                "item_id": itemId,
                "rating": rating,
                "comment": comment
            });

            if(response.success) {
                await getReviews();
                $('select[name="rating"]').val('1');
                $('textarea[name="comment"]').val('');
                $('#review-errors').html('');
            } else {
                let $errors = $('#review-errors');
                $errors.html('');
                Object.values(response.errors).forEach(errorArray => {
                    errorArray.forEach(error => {
                        $errors.append(`<div class="text-danger">${error}</div>`);
                    });
                });
            }
        } catch(error) {
            console.log(error);
        }
    });

    getReviews();
});