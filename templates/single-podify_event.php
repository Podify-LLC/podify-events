<?php
/**
 * Template: Single Podify Event
 */

get_header(); ?>

<div class="podify-single-event container">
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('podify-single-event-wrapper'); ?>>
            <?php
                $date_start = get_post_meta( get_the_ID(), '_podify_event_date_start', true );
                $date_end   = get_post_meta( get_the_ID(), '_podify_event_date_end', true );
                $date       = $date_start ? $date_start : get_post_meta( get_the_ID(), '_podify_event_date', true );
                $time       = get_post_meta( get_the_ID(), '_podify_event_time', true );
                $address    = get_post_meta( get_the_ID(), '_podify_event_address', true );
                $badge_html = '';
                if ( $date ) {
                    $now_ts   = current_time( 'timestamp' );
                    $start_ts = strtotime( $date );
                    $end_ts   = $date_end ? strtotime( $date_end ) : $start_ts;
                    if ( $start_ts ) {
                        $today      = gmdate( 'Y-m-d', $now_ts );
                        $start_day  = gmdate( 'Y-m-d', $start_ts );
                        $end_day    = gmdate( 'Y-m-d', $end_ts );
                        if ( $end_day < $today ) { $badge_html = '<div class="podify-badge" aria-hidden="true">' . esc_html__( 'Ended', 'podify-events' ) . '</div>'; }
                        elseif ( $start_day > $today ) { $badge_html = '<div class="podify-badge" aria-hidden="true">' . esc_html__( 'Upcoming', 'podify-events' ) . '</div>'; }
                        else { $badge_html = '<div class="podify-badge" aria-hidden="true">' . esc_html__( 'Ongoing', 'podify-events' ) . '</div>'; }
                    }
                }
            ?>
            <div class="podify-event-card">
                <figure class="event-image">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <?php the_post_thumbnail( 'medium_large' ); ?>
                    <?php endif; ?>
                    <?php echo $badge_html; ?>
                </figure>
                <div class="event-content">
                    <h1 class="event-title"><?php the_title(); ?></h1>
                    <div class="podify-event-meta">
                        <?php
                            if ( $date ) {
                                $human = '';
                                if ( $date_end && $date_end !== $date ) { $human = date_i18n( 'F j', strtotime( $date ) ) . '–' . date_i18n( 'j Y', strtotime( $date_end ) ); }
                                else { $human = date_i18n( 'F j Y', strtotime( $date ) ); }
                                echo '<div class="meta-item"><span class="dashicons dashicons-calendar" aria-hidden="true"></span><span class="meta-text">' . esc_html( $human . ( $time ? ' • ' . $time : '' ) ) . '</span></div>';
                            } else {
                                echo '<div class="meta-item"><span class="dashicons dashicons-calendar" aria-hidden="true"></span><span class="meta-text">' . esc_html__( 'TBD', 'podify-events' ) . '</span></div>';
                            }
                            if ( $address ) { echo '<div class="meta-item"><span class="dashicons dashicons-location" aria-hidden="true"></span><span class="meta-text">' . esc_html( $address ) . '</span></div>'; }
                        ?>
                    </div>
                    <div class="single-event-content"><?php the_content(); ?></div>
                    <?php
                        $btn_on  = get_post_meta( get_the_ID(), '_podify_event_button_enabled', true );
                        $btn_url = get_post_meta( get_the_ID(), '_podify_event_button_url', true );
                        $btn_lbl = get_post_meta( get_the_ID(), '_podify_event_button_label', true );
                        if ( $btn_on && $btn_url ) {
                            $label = $btn_lbl ? $btn_lbl : __( 'Learn more', 'podify-events' );
                            echo '<div class="event-actions"><a class="podify-read-more" href="' . esc_url( $btn_url ) . '" target="_blank" rel="noopener">' . esc_html( $label ) . '</a></div>';
                        }
                    ?>
                </div>
            </div>
        </article>
    <?php endwhile; endif; ?>
</div>

<?php get_footer(); ?>
