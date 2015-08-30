<?php
/** @var $feed BPF_Feed */
if ( isset( $feed->meta['rss_title'] ) && ! empty( $feed->meta['rss_title'] ) ) : ?>
	<div class="item-list-tabs no-ajax" id="subnav">
		<ul class="bpf_rss_feed_title">
			<li class="feed">
				<?php
				if ( isset( $feed->meta['rss_url'] ) && ! empty( $feed->meta['rss_url'] ) ) {
					echo '<a href="' . esc_url($feed->meta['rss_url']) . '" target="_blank">';
				}
				echo esc_attr( $feed->meta['rss_title'] );
				if ( isset( $feed->meta['rss_url'] ) && ! empty( $feed->meta['rss_url'] ) ) {
					echo '</a>';
				}
				?>
			</li>
		</ul>
	</div>
<?php endif; ?>