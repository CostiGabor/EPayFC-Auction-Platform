$(document).ready(function(){
    // 1.cibler le formulaire de la page
    const $form = $('form').first();
    if($form.length === 0) return; //pas de formulaire-> return

    //parcourir chaque input/textarea et stocker sa valeur initiale dans un objet indexé
    const initialValues = {};
    $form.find('input, textarea, select').each(function(){
        const name = $(this).attr('name');
        if(name){
            initialValues[name] = $(this).val();
        }
    });

    //2. verifier si au moins un champ a changé depuis le chargement, alor true
    function isFormModified(){
        let modified = false;
        $form.find('input, textarea, select').each(function(){
            const name = $(this).attr('name');
            if(name && initialValues[name] !== $(this).val()){
                modified = true;
                return false; //en .each() de jQuery = break
            }
        });
        return modified;
    }

    window.isFormModified = isFormModified;


    /*
    1.Ecouter tous les liens <a>, si un lien <a> est cliqué 
    2[Edit] Il faut verifier que les champs soient les meme que ce qui vient de la DB
    2[New] Il faut verifier que les input ne sont pas vide
    3Le modal apparait
    4[Cancel] On reste sur la page
    4[Leave] On quitte la page, on continue la requete a
    */ 
   //3. recup la modale Bootstrap(objet boostrap qui controle modale)
   const modalElem = document.getElementById('leaveConfirmModal');
   if(!modalElem) return;
   
   const leaveModal =bootstrap.Modal.getOrCreateInstance(modalElem);

   //4. sauvegarde du lien du <a> (ou user voulait aller)
   let saveNavURL = null;

   // intercepter les clics sur tous les liens <a> de la page
   $(document).on('click','a', function(e){
        if(!isFormModified()) return; // pas de modifs, on laisse passer

        //sinon on bloque et on ouvre Modale
        e.preventDefault();
        saveNavURL = $(this).attr('href');
        leaveModal.show();
   });

   //5. confirmer la navigation
   $('#leaveConfirmLeave').on('click', function(){
    if(saveNavURL){
        //desactivation de detection avant naviguer
        window.onbeforeunload = null;
        window.location.href = saveNavURL; //on lui passe le href qui a declanché le Modale
    }
   });

   //6. quand modale se ferme , vider l'url memorisé
   $(modalElem).on('hidden.bs.modal', function(){
        saveNavURL = null;
   });

   //fermeture modal
   /* $(window).on('beforeunload', function(){
        if(isFormModified())
            return ''; //return dans string même vide declenche le prompt natif
   }) */
});