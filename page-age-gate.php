<?php
/*
Template Name: 年齢認証
*/

Age_Verification::verification_init(esc_html(home_url()));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	Age_Verification::post_verification(esc_html(home_url()));
}

get_header();

?>
<?php
// Start the Loop
while (have_posts()) :
	the_post();
?>
	<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<p>あなたは●歳以上ですか</p>

		<?php the_content(); ?>

		<form action="" method="POST">
			<button type="submit">はい</button>
			<button type="button" onclick="history.back()">いいえ</button>
			<input type="hidden" name="token" value="<?php echo Age_Verification::get_token(); ?>">
		</form>
	</div><!-- #post-<?php the_ID(); ?> -->
<?php
endwhile; // End of the loop.
?>

<?php
get_footer();
