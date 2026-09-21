import './sass/input.scss';

( function( $ ) {
	function initializeField( field ) {
		// Modern ACF actions pass an acf.Field instance; legacy actions pass jQuery.
		const $field = field.$el || $( field );

		// ACF Block v3 may remount a field repeatedly. Avoid duplicate handlers.
		$field.off( '.acfDimensions' );

		// Device switcher buttons.
		$field.on( 'click.acfDimensions', '.acf-dimensions__buttons a', function( e ) {
			e.preventDefault();
			const $this = $( this );
			const deviceClass = $this.data( 'device' );

			$field.find( '.acf-dimensions__device' ).removeClass( 'acf-dimensions__device--active' );
			$field.find( '.' + deviceClass ).addClass( 'acf-dimensions__device--active' );
			$field.find( '.acf-dimensions__buttons a' ).removeClass( 'btn--active' );
			$this.addClass( 'btn--active' );
		} );

		// Sync top value to bottom when linked.
		$field.on( 'keyup.acfDimensions', '.input-top', function() {
			const $this = $( this );
			const $device = $this.closest( '.acf-dimensions__device' );
			const $isLinked = $device.find( '.btn--linker' ).hasClass( 'btn--active' );

			if ( true === $isLinked ) {
				handleTopChange( $this );
			}
		} );

		// Link/unlink button.
		$field.on( 'click.acfDimensions', '.acf-dimensions__linker .btn--linker', function( e ) {
			e.preventDefault();
			const $this = $( this );
			const $isActive = $this.hasClass( 'btn--active' );
			const $linker = $this.closest( '.acf-dimensions__linker' );

			if ( true === $isActive ) {
				// Unlink.
				makeUnlinked( $this );
				$linker.find( '.input-linked' ).val( '0' );
				copyLinkedValue( $this );
			} else {
				// Link.
				makeLinked( $this );
				$linker.find( '.input-linked' ).val( '1' );
				copyLinkedValue( $this );
			}

			$this.toggleClass( 'btn--active' );
		} );

		function handleTopChange( $this ) {
			const $valueTop = $this.val();
			const $texts = $this.closest( '.acf-dimensions__texts' );

			$texts.find( 'input:not(.input-linked)' ).val( $valueTop );
		}

		function copyLinkedValue( $this ) {
			const $inputs = $this.closest( '.acf-dimensions__inputs' );
			const $valueTop = $inputs.find( '.input-top' ).val();

			if ( $valueTop ) {
				$inputs.find( '.acf-dimensions__texts input:not(.input-linked)' ).val( $valueTop );
			}
		}

		function makeLinked( $this ) {
			const $inputs = $this.closest( '.acf-dimensions__inputs' );
			$inputs.find( '.acf-dimensions__texts input:not(.input-top)' ).prop( 'readonly', true );
		}

		function makeUnlinked( $this ) {
			const $inputs = $this.closest( '.acf-dimensions__inputs' );
			$inputs.find( '.acf-dimensions__texts input:not(.input-top)' ).prop( 'readonly', false );
		}
	}

	if ( typeof acf.addAction !== 'undefined' ) {
		acf.addAction( 'ready_field/type=dimensions', initializeField );
		acf.addAction( 'append_field/type=dimensions', initializeField );
		acf.addAction( 'remount_field/type=dimensions', initializeField );
	} else if ( typeof acf.add_action !== 'undefined' ) {
		acf.add_action( 'ready_field/type=dimensions', initializeField );
		acf.add_action( 'append_field/type=dimensions', initializeField );
		acf.add_action( 'remount_field/type=dimensions', initializeField );
	}
}( jQuery ) );
