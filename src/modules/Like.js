import $ from 'jquery';

class Like {
    constructor() {
        this.events();
    }

    events() {
        $(".like-box").on('click', this.clickDispatcher.bind(this));
    }

    // Methods
    clickDispatcher(e) {
        // bit of an overkill if interface would be done differently
        // but still usefult if there are a lot of same like-boxes on the page
        const currentLikeBox = $(e.target).closest(".like-box");

        if (currentLikeBox.attr('data-exists') == 'yes') {
            this.deleteLike(currentLikeBox);
        } else {
            this.createLike(currentLikeBox);
        }
    }

    createLike(currentLikeBox) {
        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/university/v1/manageLike',
            type: 'POST',
            data: { 'professorId': currentLikeBox.data('professor') },
            success: (response) => {
                currentLikeBox.attr("data-exists", "yes");
                let likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                likeCount++;
                currentLikeBox.find(".like-count").html(likeCount);
                currentLikeBox.attr("data-like", response);
                console.log(response)
            },
            error: (err) => { console.log("Create failed!"); console.log(err) }
        })
    }

    deleteLike(currentLikeBox) {
        $.ajax({
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', universityData.nonce);
            },
            url: universityData.root_url + '/wp-json/university/v1/manageLike',
            data: { 'like': currentLikeBox.data('like') },
            type: 'DELETE',
            success: (response) => {
                currentLikeBox.attr("data-exists", "no");
                let likeCount = parseInt(currentLikeBox.find(".like-count").html(), 10);
                likeCount--;
                currentLikeBox.find(".like-count").html(likeCount);
                currentLikeBox.attr("data-like", "");
                console.log(response)
            },
            error: (err) => { console.log("Delete failed!"); console.log(err) }
        })
    }
}

export default Like;