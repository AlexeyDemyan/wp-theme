import $ from 'jquery';

class MyNotes {
    // 1. Constructor
    constructor() {
        this.events();
    }

    // 2. Events
    events() {
        // Previously we used a different jQuery selector here
        // Now changed it to add event listener to my-notes
        // To accrue for notes created in the future
        $("#my-notes").on("click", ".delete-note", this.deleteNote);
        $("#my-notes").on("click", ".edit-note", this.editNote.bind(this));
        $("#my-notes").on("click", ".update-note", this.updateNote.bind(this));
        $(".submit-note").on("click", this.createNote.bind(this));
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
            success: (response) => {
                thisNote.slideUp();
                console.log('delete worked!');
                console.log(response);
                if (response.userNoteCount < 5) {
                    $(".note-limit-message").removeClass("active");
                }
            },
            error: (err) => { console.log('Delete request failed'); console.log(err) }
        });
    }

    updateNote(e) {
        const thisNote = $(e.target).parents("li");

        const updatedPost = {
            'title': thisNote.find(".note-title-field").val(),
            'content': thisNote.find(".note-body-field").val(),
        };

        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            // thisNote.data seems to be a jQuery alternative to "dataset"
            url: universityData.root_url + '/wp-json/wp/v2/note/' + thisNote.data('id'),
            type: 'POST',
            data: updatedPost,
            // jqeury slideUp is a cool method that removes element with animation
            success: (response) => { this.makeNoteReadOnly(thisNote); console.log('update worked!'); console.log(response) },
            error: (err) => { console.log('Update request failed'); console.log(err) }
        });
    }

    createNote() {
        const newPost = {
            'title': $(".new-note-title").val(),
            'content': $(".new-note-body").val(),
            // Otherwise new notes will be in Draft:
            'status': 'publish'
        };

        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            // thisNote.data seems to be a jQuery alternative to "dataset"
            url: universityData.root_url + '/wp-json/wp/v2/note/',
            type: 'POST',
            data: newPost,
            // jqeury slideUp is a cool method that removes element with animation
            success: (response) => {
                $(".new-note-title, .new-note-body").val("");
                // Cool way to render newly created note in the #my-notes list:
                $(`
                    <li data-id="${response.id}">
                    <input readonly class="note-title-field" type="text" value="${response.title.raw}">
                    <span class="edit-note"><i class="fa fa-pencil" aria-hidden="true"></i> Edit</span>
                    <span class="delete-note"><i class="fa fa-trash-o" aria-hidden="true"></i>Delete</span>
                    <textarea readonly class="note-body-field">${response.content.raw}</textarea>
                    <span class="update-note btn btn--blue btn--small"><i class="fa fa-arrow-right" aria-hidden="true"></i>Save</span>
                </li>
                    `).prependTo("#my-notes").hide().slideDown();
                console.log('create worked!'); console.log(response)
            },
            error: (err) => {
                console.log('Create request failed');
                $(".note-limit-message").addClass("active");
                console.log(err)
            }
        });
    }

    editNote(e) {
        const thisNote = $(e.target).parents("li");
        if (thisNote.data("state") === "editable") {
            this.makeNoteReadOnly(thisNote)
        } else {
            this.makeNoteEditable(thisNote)
        }
    }

    makeNoteEditable(thisNote) {
        thisNote.find(".edit-note").html('<i class="fa fa-times" aria-hidden="true"></i> Cancel');
        // Cool jQuery method to select multiple elements and remove attribute from all of them
        // And we're also using chaining here to add Class in the same method, cool:
        thisNote.find(".note-title-field, .note-body-field").removeAttr('readonly').addClass('note-active-field');
        thisNote.find(".update-note").addClass("update-note--visible");
        thisNote.data("state", "editable");
    }

    makeNoteReadOnly(thisNote) {
        thisNote.find(".edit-note").html('<i class="fa fa-pencil" aria-hidden="true"></i> Edit');
        // Also here we're assiging readonly value to readonly attribute, looks funny:
        thisNote.find(".note-title-field, .note-body-field").attr('readonly', 'readonly').removeClass('note-active-field');
        thisNote.find(".update-note").removeClass("update-note--visible");
        thisNote.data("state", "cancel");
    }

}

export default MyNotes;