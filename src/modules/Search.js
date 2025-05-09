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

        // This is done as part of non-JS Search fallback functionality
        // This prevents default behaviour of link elements (<a>) as we have now changed from <span> to <a>
        return false;
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
        $.getJSON(universityData.root_url + '/wp-json/university/v1/search?keyword=' + this.searchField.val(), (results) => {
            this.resultsDiv.html(`
                <div class="row">
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">General Information</h2>
                        ${results.generalInfo.length ?
                    `<ul class="link-list min-list">
                            ${results.generalInfo.map((result) => {
                        return `<li><a href="${result.permalink}">${result.title}</a> ${result.postType == 'post' ? `by ${result.authorName}` : ''}</li>`
                    }).join('')}
                            </ul>` : "<p>No General Information matches the search</p>"}
                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Programs</h2>
                        ${results.programs.length ?
                    `<ul class="link-list min-list">
                                    ${results.programs.map((result) => {
                        return `<li><a href="${result.permalink}">${result.title}</a></li>`
                    }).join('')}
                                    </ul>` : `<p>No Programs match the search. <a href="${universityData.root_url}/programs">View All Programs</a></p>`}

                        <h2 class="search-overlay__section-title">Professors</h2>
                        ${results.professors.length ?
                    `<ul class="professor-cards">
                                            ${results.professors.map((result) => {
                        return `
                        <li class="professor-card__list-item">
                    <a class="professor-card" href="${result.permalink}">
                        <img class="professor-card__image" src="${result.image}" alt="">
                        <span class="professor-card__name">${result.title}</span>
                    </a>
                </li>
                        `
                    }).join('')}
                                            </ul>` : `<p>No Professors match the search</p>`}

                    </div>
                    <div class="one-third">
                        <h2 class="search-overlay__section-title">Campuses</h2>
                        ${results.campuses.length ?
                    `<ul class="link-list min-list">
                                            ${results.campuses.map((result) => {
                        return `<li><a href="${result.permalink}">${result.title}</a></li>`
                    }).join('')}
                                            </ul>` : `<p>No Campuses match the search. <a href="${universityData.root_url}/campuses">View All Campuses</a></p>`}
                        

                        <h2 class="search-overlay__section-title">Events</h2>
                        ${results.events.length ?
                    `${results.events.map((result) =>
                        `<div class="event-summary">
    <a class="event-summary__date t-center" href="${result.permalink}">
        <span class="event-summary__month">${result.month}</span>
        <span class="event-summary__day">${result.day}</span>
    </a>
    <div class="event-summary__content">
        <h5 class="event-summary__title headline headline--tiny"><a href="${result.permalink}">${result.title}</a></h5>
        <p>${result.description}<a href="${result.permalink}" class="nu gray"> Learn more</a></p>
    </div>
</div>
                        `
                    ).join('')}` : `<p>No Events match the search. <a href="${universityData.root_url}/events">View All Events</a></p>`}

                    </div>
                </div>
                `);
            this.isSpinnerVisible = false;
        });

        // $.when(
        //     $.getJSON(universityData.root_url + '/wp-json/wp/v2/pages?search=' + this.searchField.val()),
        //     $.getJSON(universityData.root_url + '/wp-json/wp/v2/posts?search=' + this.searchField.val())
        // ).then((pages, posts) => {
        //     let combinedResults = pages[0].concat(posts[0]);
        //     this.resultsDiv.html(`
        //             <h2 class="search-overlay__section-title">General Information</h2>
        //             ${combinedResults.length ?
        //             `<ul class="link-list min-list">
        //             ${combinedResults.map((result) => {
        //                 return `<li><a href="${result.link}">${result.title.rendered}</a> ${result.type == 'post' ? `by ${result.authorName}` : ''}</li>`
        //             }).join('')}
        //             </ul>` : "<p>No general information matches the search</p>"}
        //             `);
        //     this.isSpinnerVisible = false;
        // }, () => {
        //     this.resultsDiv.html('<p>Unexpected error. Please try again</p>');
        // });
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