var ReferrerAnalytics = {
  /**
   * Get a URL parameter
   *
   * @param {string} param The URL parameter to retrieve
   */
  getURLParam: function( param ) {
    var value = false;

    // Get the current URL parameters
    let urlParams = window.location.href.split( '?' )[1];

    // Get param if available
    if ( urlParams ) {
      const params = new URLSearchParams( urlParams );
      value = params.get( param );
    } else {
      // Check HTTP referrer for param
      urlParams = document.referrer.split( '?' )[1];

      if ( urlParams ) {
        var params = new URLSearchParams( urlParams );
        value = params.get( param );
      }
    }

    return value;
  },

  /**
   * Set current URL referrer cookies
   */
  setCookies: function() {
    var utm_source   = this.getURLParam( 'utm_source' );
    var utm_medium   = this.getURLParam( 'utm_medium' );
    var utm_campaign = this.getURLParam( 'utm_campaign' );
    var utm_term     = this.getURLParam( 'utm_term' );
    var utm_content  = this.getURLParam( 'utm_content' );

    if ( utm_source ) {
      Cookies.remove( 'referrer-analytics-utm_source' );
      Cookies.set( 'referrer-analytics-utm_source', utm_source );
    }

    if ( utm_medium ) {
      Cookies.remove( 'referrer-analytics-utm_medium' );
      Cookies.set( 'referrer-analytics-utm_medium', utm_medium );
    }

    if ( utm_campaign ) {
      Cookies.remove( 'referrer-analytics-utm_campaign' );
      Cookies.set( 'referrer-analytics-utm_campaign', utm_campaign );
    }

    if ( utm_term ) {
      Cookies.remove( 'referrer-analytics-utm_term' );
      Cookies.set( 'referrer-analytics-utm_term', utm_term );
    }

    if ( utm_content ) {
      Cookies.remove( 'referrer-analytics-utm_content' );
      Cookies.set( 'referrer-analytics-utm_content', utm_content );
    }
  },

  /**
   * Initialization
   */
  init: function() {
    this.setCookies()
  }
};

ReferrerAnalytics.init();
