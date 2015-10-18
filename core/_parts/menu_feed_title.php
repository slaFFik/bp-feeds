<?php
/** @var $feed BPF_Feed */
?>
<?php
if ( ! empty( $feed->meta['site_url'] ) ) : ?>
	<div class="item-list-tabs no-ajax" id="subnav">
		<ul class="bpf_feed_title">
			<li class="feed">
				<?php
				if ( ! empty( $feed->meta['site_url'] ) ) {
					echo '<a href="' . esc_url($feed->meta['site_url']) . '" target="_blank">';
				}

				echo esc_attr( $feed->meta['site_title'] );

				if ( ! empty( $feed->meta['site_url'] ) ) {
					echo '</a>';
				}
				?>
			</li>
		</ul>
	</div>
<?php endif; ?>