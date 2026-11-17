$(document).ready(async function () {
    //on fait en sorte d'executer que si on est dans add_edit_item grace au champ "title"
    if($('#itemTitle').length === 0) return;

    //charger les regles depuis server
    let rules;
    try{
        rules = await $.get('item/getRules');
    }catch(e){
        console.error("Impossible de charger les regles de validation",e);
        return;
    }

    
    // Conversion des types : parse_ini_file renvoie des strings, on convertit en nombres
    rules.title_min_lenght = parseInt(rules.title_min_lenght);
    rules.description_min_lenght = parseInt(rules.description_min_lenght);
    rules.duration_days_min = parseInt(rules.duration_days_min);
    rules.duration_days_max = parseInt(rules.duration_days_max);
    rules.starting_bid_min = parseFloat(rules.starting_bid_min);
    rules.buy_now_price_min = parseFloat(rules.buy_now_price_min);

    //conversion de la regex du format PHP vers JS
    const regexStr = rules.starting_bid_now_price_format.replace(/^\/|\/$/g, '');
    rules.priceRegex = new RegExp(regexStr);
   
    //functions de validation qui retournent Promise<Array<string>>

    async function validateTitle(value){
        const errors = [];
        const trimValue = value.trim();

        if(trimValue === ''){
            errors.push("Title is required!");
            return errors;
        }

        if(trimValue.length < rules.title_min_lenght){
            errors.push(`Title must be at least ${rules.title_min_lenght} characters long.`);
        }
        //to do - assync check (AJAX)
        const itemId = parseInt($('#itemId').val()) || 0;
        try{
            const response = await $.post(
                `item/checkTitleUnique`,{
                    title: trimValue,
                    itemId: itemId
        });
            if(!response.unique){
                errors.push("You already have an item with this title");
            }
        }catch(e){
            console.error("Erreur lors de la verification d'unicite du title",e);
        }
        
        return errors;
    }

    async function validateDescription(value) {
        const errors = [];
        const trimValue = value.trim();

        // Champ optionnel : si vide, c'est valide
        if (trimValue === '') return errors;

        if (trimValue.length < rules.description_min_lenght) {
        errors.push(`Description must be at least ${rules.description_min_lenght} characters long.`);
        }

    return errors;
    }

    async function validateSaleDuration(value) {
        const errors = [];
        const trimValue= value.trim();

        if(trimValue ===''){
            errors.push("Sale duration is required.");
            return errors;
        }

        const duration = parseInt(trimValue);
        if(isNaN(duration)){
            errors.push("Sale duration must be a valid number.");
            return errors;
        }

        if(duration < rules.duration_days_min || duration > rules.duration_days_max){
            errors.push(`Sale duration must be between ${rules.duration_days_min} and ${rules.duration_days_max} days.`);
        }
        return errors;

    }    

    async function validateStartingBid(value) {
        const errors = [];
        const trimValue = value.trim();

        // Optionnel
        if (trimValue === '') return errors;

        // Format
        if (!rules.priceRegex.test(trimValue)) {
            errors.push("Starting bid must be a valid number (e.g. 40 or 40.00).");
            return errors;
        }

        // On normalise la virgule en point pour parseFloat (la regex accepte les deux)
        const num = parseFloat(trimValue.replace(',', '.'));
        if (num <= rules.starting_bid_min) {
            errors.push(`Starting bid must be greater than ${rules.starting_bid_min}.`);
        }

        return errors;
    }

    async function validateBuyNowPrice(value, startingBidValue) {
        const errors = [];
        const trimValue = value.trim();

        if (trimValue === '') return errors;

        if (!rules.priceRegex.test(trimValue)) {
            errors.push("Buy now price must be a valid number.");
            return errors;
        }

        const num = parseFloat(trimValue.replace(',','.'));
        if(num <= rules.buy_now_price_min){
            errors.push(`Buy now price must be greater than ${rules.buy_now_price_min}.`);
        }

        //buy_now > starting_bid
        const startingBidStr = startingBidValue ?? '';
        if(startingBidStr !== '' && rules.priceRegex.test(startingBidStr)){
            const startingBid = parseFloat(startingBidStr.replace(',','.'));
            if(num <= startingBid){
                errors.push("Buy now price must be greater than starting bid.");
            }
        }
        return errors;
    }

    async function validateDirectSale(value) {
        const errors = [];
        const trimValue = value.trim();

        if (trimValue === '') return errors;

        if (!rules.priceRegex.test(trimValue)) {
            errors.push("Direct sale price must be a valid number (e.g. 150 or 150.00).");
            return errors;
        }

        const num = parseFloat(trimValue.replace(',', '.'));
        if(num <= rules.buy_now_price_min){
            errors.push(`Direct sale price must be greater than ${rules.buy_now_price_min}.`);
        }

        return errors;
    }

    async function validateSaleType(startingBidValue , directSaleValue) {
        const errors = [];
        if(startingBidValue.trim() === '' && directSaleValue.trim() === ''){
            errors.push("You must fill either an auction or a direct sale price.");
        }
        return errors;
    }

    function applyFieldFeedback(input ,fieldName, errors , isTouched){
        const feedback = $('#' + fieldName + '-feedback');

       //nettoyer d'abord toujours
       input.removeClass('is-valid is-invalid');
       feedback.text('');
       
       if(!isTouched) return; // si aucun champ touché, return

       if(errors.length > 0){
            input.addClass('is-invalid');
            feedback.text(errors[0]);
       }else{
            input.addClass('is-valid');
       }
    }

    async function updateSaleTypeFeedback(){
        const errors = await validateSaleType(
            $('#startingBid').val(),
            $('#salePrice').val()
        );

        const oneTouched = touchedFields.has('starting_bid') || touchedFields.has('direct_sale');
        const $feedback = $('#sale_type-feedback');

        if(oneTouched && errors.length > 0){
            $feedback.text(errors[0]);
        }else
            $feedback.text('');
    }

    // memoriser les champs touchés 
    const touchedFields = new Set();

    //validation d'un champ et application le feedback
    async function validateField(fieldName) {
        const $input = $('#' + getInputId(fieldName));
        let errors = [];

        switch (fieldName){
            case 'title':
                errors = await validateTitle($input.val());
                break;
            case 'description':
                errors = await validateDescription($input.val());
                break;
            case 'starting_bid':
                errors = await validateStartingBid($input.val());
                break;
            case 'buy_now_price':
                errors = await validateBuyNowPrice($input.val(), $('#startingBid').val());
                break;
            case 'direct_sale':
                errors = await validateDirectSale($input.val());
                break;
            case 'sale_duration':
                errors = await validateSaleDuration($input.val());
                break;    
        }

        applyFieldFeedback($input, fieldName , errors , touchedFields.has(fieldName));
    }

    function getInputId(fieldName){
        const map ={
            title: 'itemTitle',
            description: 'itemDescription',
            sale_duration: 'itemDays',
            starting_bid: 'startingBid',
            buy_now_price: 'instantPurchasePrice',
            direct_sale: 'salePrice',
        };
        return map[fieldName];
    }

    //connection les events pour chaque field
    const fieldNames = ['title', 'description', 'sale_duration', 'starting_bid', 'buy_now_price', 'direct_sale'];

    fieldNames.forEach(name =>{
        const $input = $('#' + getInputId(name));

        const handler = async function(){
            touchedFields.add(name);
            await validateField(name);
            if(name === 'starting_bid' || name === 'direct_sale'){
                await updateSaleTypeFeedback();
            }
            await updateSaveBtn();
        }
        // touched + on valide
        $input.on('input', handler);

        $input.on('input', handler);

        
    })

    async function validateForm() {
        const allErrors = await Promise.all([
            validateTitle($('#itemTitle').val()),
            validateDescription($('#itemDescription').val()),
            validateSaleDuration($('#itemDays').val()),
            validateStartingBid($('#startingBid').val()),
            validateBuyNowPrice($('#instantPurchasePrice').val(), $('#startingBid').val()),
            validateDirectSale($('#salePrice').val()),
            validateSaleType($('#startingBid').val(), $('#salePrice').val())
        ]);

        const hasErrors = allErrors.some(errors => errors.length > 0);
        return !hasErrors;
    }

    //mise à jour du btn Save
    async function updateSaveBtn() {
        const isValid = await validateForm();
        $('#saveBtn').prop('disabled', !isValid);
    }

    //au chargement de page, ex en add_item saveBtn Off car les champs sont vides
    await updateSaveBtn();

    window.validateDescription = validateDescription;
    window.validateSaleDuration = validateSaleDuration;
    window.validateStartingBid = validateStartingBid;
    window.validateBuyNowPrice = validateBuyNowPrice;
    window.validateDirectSale = validateDirectSale;
    window.validateSaleType = validateSaleType; 
})