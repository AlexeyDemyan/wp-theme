import $ from 'jquery';

class Search {
    // 1. Constructor
    constructor() {
        this.addSearchHTML();
        this.resultsDiv = $("#search-overlay__results");
        this.openButton = $(".js-search-trigger");
        this.closeButton = $(".search-overlay__close");
        this.searchOverlay = $(".search-overlay");
        this.searchField = $("#search-term");
        this.isOverlayOpen = false;
        this.isSpinnerVisible = false;
        this.typingTimer;
        this.previousValue;

        this.events();
    }

    // 2. Events
    events() {
        this.openButton.on('click', this.openOverlay.bind(this));
        this.closeButton.on('click', this.closeOverlay.bind(this));
        $(document).on("keydown", this.keyPressDispatcher.bind(this));
        this.searchField.on("keyup", this.typingLogic.bind(this));
    }

    // 3. Methods
    openOverlay() {
        this.searchOverlay.addClass('search-overlay--active');
        $("body").addClass('body-no-scroll');
        this.searchField.val("");
        setTimeout(() => {
            this.searchField.focus();
        }, 350);
        this.isOverlayOpen = true;
    }

    closeOverlay() {
        this.searchOverlay.removeClass('search-overlay--active');
        $("body").removeClass('body-no-scroll');
        this.isOverlayOpen = false;
    }

    keyPressDispatcher(e) {
        if (e.keyCode === 83 && !this.isOverlayOpen && !$("input, textarea").is(':focus')) {
            this.openOverlay();
        }
        if (e.keyCode === 27 && this.isOverlayOpen) {
            this.closeOverlay();
        }
    }

    getResults() {
        $.when(
            $.getJSON(universityData.root_url + '/wp-json/wp/v2/pages?search=' + this.searchField.val()),
            $.getJSON(universityData.root_url + '/wp-json/wp/v2/posts?search=' + this.searchField.val())
        ).then((pages, posts) => {
            let combinedResults = pages[0].concat(posts[0]);
            this.resultsDiv.html(`
                    <h2 class="search-overlay__section-title">General Information</h2>
                    ${combinedResults.length ?
                    `<ul class="link-list min-list">
                    ${combinedResults.map((result) => {
                        return `<li><a href="${result.link}">${result.title.rendered}</a> ${result.type == 'post' ? `by ${result.authorName}` : ''}</li>`
                    }).join('')}
                    </ul>` : "<p>No general information matches the search</p>"}
                    `);
            this.isSpinnerVisible = false;
        }, () => {
            this.resultsDiv.html('<p>Unexpected error. Please try again</p>');
        });
    }

    typingLogic() {
        if (this.searchField.val() != this.previousValue) {

            clearTimeout(this.typingTimer);

            if (this.searchField.val()) {
                if (!this.isSpinnerVisible) {
                    this.resultsDiv.html('<div class="spinner-loader"></div>');
                    this.isSpinnerVisible = true;
                }

                this.typingTimer = setTimeout(this.getResults.bind(this), 500);
            } else {
                this.resultsDiv.html('');
                this.isSpinnerVisible = false;
            }
        }
        this.previousValue = this.searchField.val();
    }

    addSearchHTML() {
        $("body").append(`<div class="search-overlay">
    <div class="search-overlay__top">
        <div class="container">
            <i class="fa fa-search search-overlay__icon" aria-hidden="true"></i>
            <input id="search-term" type="text" class="search-term" placeholder="What are you looking for?">
            <i class="fa fa-window-close search-overlay__close" aria-hidden="true"></i>
        </div>
    </div>
    <div class="container">
        <div id="search-overlay__results"></div>
    </div>
</div>`);
    }
}

export default Search;