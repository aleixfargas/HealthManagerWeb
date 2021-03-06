function get_locale(){
    return $("body").attr('lang');
}

var Translator = {
    locale_: 'en',
    locale: function(locale){
        if(locale !== null) this.locale_ = locale;
        return this.locale_;
    },
    translations: {
        ca: {
            title_sure: "Estàs segur?",
            title_deleted: "S'ha esborrat!",
            title_error: "Error!",
            text_noRevert: "No podràs desfer aquests canvis!",
            text_patient_deleted: "Els pacients seleccionats han estat eliminats",
            text_visit_deleted: "Les visites seleccionades han estat eliminades",
            text_operation_deleted: "Les operacions seleccionades han estat eliminades",
            text_allergy_deleted: "Les al·lèrgies seleccionades han estat eliminades",
            text_error_noPatientSelected: "Primer tria un pacient per eliminar",
            text_error_noVisitSelected: "Primer tria una visita per eliminar",
            text_error_noOperationSelected: "Primer tria una operació per eliminar",
            text_error_noAllergySelected: "Primer tria una al·lèrgia per eliminar",
            button_delete: "Esborrar",
            error_loading_visits: "Error carregant les visites per el dia seleccionat, siusplau recarrega la pàgina",
            title_add_new_fast_visit: "Afegeix una visita",
            body_add_new_fast_visit: "Vols afegir una visita?",
            button_add_new_fast_visit: "Afegir",
            button_cancel_add_new_fast_visit: "Cancelar",
            incorrect_phone_length: "Numero de telefon incorrecte",
        },
        en: {
            title_sure: "Are you sure?",            
            title_deleted: "Deleted!",
            title_error: "Error!",
            text_noRevert: "You won't be able to revert this!",
            text_patient_deleted: "The selected patients has been deleted",
            text_visit_deleted: "The selected visits has been deleted",
            text_operation_deleted: "The selected operations has been deleted",
            text_allergy_deleted: "The selected allergies has been deleted",
            text_error_noPatientSelected: "First select a patient to delete",
            text_error_noVisitSelected: "First select a visit to delete",
            text_error_noOperationSelected: "First select an operation to delete",
            text_error_noAllergySelected: "First select an allergy to delete",
            button_delete: "Delete",
            error_loading_visits: "Error loading the visits for the selected date, please reload the page",
            title_add_new_fast_visit: "Add a new visit",
            body_add_new_fast_visit: "Do you want to add a new visit?",
            button_add_new_fast_visit: "Add",
            button_cancel_add_new_fast_visit: "Cancel",
            incorrect_phone_length: "Numero de telefono no valido",
        },
        es: {
            title_sure: "¿Estás seguro?",
            title_deleted: "Se ha borrado!",
            title_error: "Error!",
            text_noRevert: "Usted no será capaz de revertir esto!",
            text_patient_deleted: "Se ha eliminado a los pacientes",
            text_visit_deleted: "Se han eliminado las visitas selecionadas",
            text_operation_deleted: "Se han eliminado las operaciones selecionadas",
            text_allergy_deleted: "Se han eliminado las alergias selecionadas",
            text_error_noPatientSelected: "Primero seleccione un paciente para borrar",
            text_error_noVisitSelected: "Primero seleccione una visita para borrar",
            text_error_noOperationSelected: "Primero seleccione una operacion para borrar",
            text_error_noAllergySelected: "Primero seleccione una alergia para borrar",
            button_delete: "Borrar",
            error_loading_visits: "Error cargando las visitas para el dia seleccionado, por favor, recarga la pagina",
            title_add_new_fast_visit: "Añade una visita",
            body_add_new_fast_visit: "Quieres añadir una nueva visita?",
            button_add_new_fast_visit: "Añadir",
            button_cancel_add_new_fast_visit: "Cancelar",
            incorrect_phone_length: "Phone number not valid",
        }
    },
    trans: function(trans_id){
        var translation = this.translations[this.locale_][trans_id];
        if(translation === undefined || translation === null) translation = "undefined";
        
        return translation;
    }
};