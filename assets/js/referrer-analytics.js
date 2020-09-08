var ReferrerAnalytics = {
  /**
   * Get a URL parameter
   *
   * @param {string} param The URL parameter to retrieve
   */
  getURLParam: function( param ) {
		const params = new URLSearchParams( window.location.href.split( '?' )[1] ? window.location.href.split( '?' )[1] : document.referrer.split( '?' )[1] );
		return params.get( param );
  },

  /**
   * Set current URL referrer cookies
   */
  setCookies: function() {
		[ 'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content' ].forEach(item => {
			const urlParam = this.getURLParam( item );
			if ( urlParam ) {
				Cookies.remove( 'referrer-analytics-' + item );
				Cookies.set( 'referrer-analytics-' + item, urlParam );
			}
		});
  }
};

ReferrerAnalytics.setCookies();
