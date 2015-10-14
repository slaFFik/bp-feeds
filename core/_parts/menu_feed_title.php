<?php
/** @var $feed BPF_Feed */
?>
<?php
if ( ! empty( $feed->meta['rss_title'] ) ) : ?>
	<div class="item-list-tabs no-ajax" id="subnav">
		<ul class="bpf_feed_title">
			<li class="feed">
				<?php
				if ( ! empty( $feed->meta['rss_url'] ) ) {
					echo '<a href="' . esc_url($feed->meta['rss_url']) . '" target="_blank">';
				}

				echo esc_attr( $feed->meta['rss_title'] );

				if ( ! empty( $feed->meta['rss_url'] ) ) {
					echo '</a>';
				}
				?>
			</li>
		</ul>
	</div>
<?php endif; ?>