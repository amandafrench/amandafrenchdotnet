<?php
/**
 * The template for displaying the footer.
 *
 * @package ns-minimal
 * @since ns-minimal 1.0.0
 */
?>

		</div><!-- #content -->

	</div><!-- .container -->

		<footer id="colophon" class="site-footer" role="contentinfo">

			<div class="container">

				<div class="site-info">

					<?php get_sidebar('footer'); ?>

					<?php if ( get_theme_mod( 'ns_minimal_footer' ) ) : ?>

						<?php echo esc_attr( get_theme_mod( 'ns_minimal_footer' ) ); ?>

					<?php else : ?>

						<a href="<?php echo esc_url( __( 'https://wordpress.org/', 'ns-minimal' ) ); ?>">
							<?php
							/* translators: %s: CMS name, i.e. WordPress. */
							printf( esc_html__( 'Proudly powered by %s', 'ns-minimal' ), 'WordPress' );
							?>
						</a>
						<?php echo esc_html( ns_minimal_author_text_credit() ); ?>

					<?php endif; ?>

				</div><!-- .site-info -->

			</div><!-- .container -->

		</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
