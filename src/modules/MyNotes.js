import $ from 'jquery';

class MyNotes {
    // 1. Constructor
    constructor() {
        this.events();
    }

    // 2. Events
    events() {
        // This jQuery selector conveniently selects ALL elements with .delete-note class
        // and conveniently places separate event listeners on them
        // instead of looping through QuerySelectAll
        $(".delete-note").on("click", this.deleteNote);
        $(".edit-note").on("click", this.editNote);
    }

    // 3. Methods
    deleteNote(e) {
        const thisNote = $(e.target).parents("li");

        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            // thisNote.data seems to be a jQuery alternative to "dataset"
            url: universityData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
            type: 'DELETE',
            // jqeury slideUp is a cool method that removes element with animation
            success: (response) => {thisNote.slideUp(); console.log('delete worked!'); console.log(response) },
            error: (err) => { console.log('Delete request failed'); console.log(err) }
        });
    }

    editNote(e) {
        const thisNote = $(e.target).parents("li");
        // Cool jQuery method to select multiple elements and remove attribute from all of them
        // And we're also using chaining here to add Class in the same method, cool:
        thisNote.find(".note-title-field, .note-body-field").removeAttr('readonly').addClass('note-active-field');
        thisNote.find(".update-note").addClass("update-note--visible");
    }

}

export default MyNotes;