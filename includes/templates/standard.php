        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-content">

                <div id="logo">
                    <?php $business_info = iwp_get_business_information(); ?>

                    <?php if ( ! empty( $business_info['business_logo'] ) ) : ?>
                        <img src="<?php echo $business_info['business_logo']; ?>">
                    <?php endif; ?>
                </div>

                <h1><?php if ( $invoiceContent['isQuote'] == 1 ) { _e( 'Quote', 'iwp-txt' ); } else { _e( 'Invoice', 'iwp-txt' ); } ?></h1>
                <div id="company" style="width: 49%; float: left; min-width: 49%;">
                    <div><?php echo $business_info['business_name']; ?></div>
                    <div><?php echo $business_info['business_address1']; ?><br>
                    <?php if( ! empty( $business_info['business_address2'] ) ) { echo $business_info['business_address2'] . "<br>"; } ?>
                    <?php echo $business_info['business_city']; ?> <?php echo $business_info['business_state']; ?>
                    <?php if ( ! empty( $business_info['business_zip_code'] ) || ! empty( $business_info['business_country'] ) ) : ?>
                        , <?php echo $business_info['business_zip_code']; ?><br><?php echo $business_info['business_country']; ?></div>
                    <?php endif; ?>

                    <div><?php echo $business_info['business_phone_number']; ?></div>
                    <div><a href="mailto:<?php echo $business_info['business_email']; ?>"><?php echo $business_info['business_email']; ?></a></div>
                </div>

                <div id="project" style="width: 50%; float: right;">
                    <div><span><?php _e( 'Project:', 'iwp-txt' ); ?></span> <?php echo get_the_title(); ?></div>
                    <?php if( ! empty( $invoiceContent['user_data']['company_name'] ) ) { ?><div><span><?php _e( 'Client:', 'iwp-txt' ); ?></span> <?php echo $invoiceContent['user_data']['company_name']; ?></div><?php } ?>
                    <div><span><?php _e( 'Name:', 'iwp-txt' ); ?></span> <?php echo $invoiceContent['user_data']['first_name'] . '  ' . $invoiceContent['user_data']['last_name']; ?></div>
                    <div><span><?php _e( 'Address:', 'iwp-txt' ); ?></span><br /> <?php echo $invoiceContent['user_data']['streetaddress'] . ' '; if( ! empty( $iwp_options['business_address2'] ) ) { echo $invoiceContent['user_data']['streetaddress2'] . ' '; } echo $invoiceContent['user_data']['city'] . ', ' . $invoiceContent['user_data']['state'] . ', ' . $invoiceContent['user_data']['zip']; ?></div>
                    <div><span><?php _e( 'Email:', 'iwp-txt' ); ?></span> <a href="<?php echo $invoiceContent['user_data']['user_email']; ?>"><?php echo $invoiceContent['user_data']['user_email']; ?></a></div>
                    <div><span><?php _e( 'Phone:', 'iwp-txt' ); ?></span> <?php echo $invoiceContent['user_data']['phonenumber']; ?></div>
                    <div><span><?php _e( 'DATE:', 'iwp-txt' ); ?></span> <?php echo the_time('F j, Y'); ?></div>
                    <?php if( $invoiceContent['paymentDueDate'] == 1 ) { ?>
                        <div><strong><span><?php _e( 'Due Date:', 'iwp-txt' ); ?></span> <?php echo $invoiceContent['paymentDueDateText']; ?></strong></div>
                    <?php } ?>
                    <?php if( $invoiceContent['minPayment'] == 1 ) { ?>
                        <div style="font-size: 0.8em;"><span><?php _e( 'Minimum Payment Due:', 'iwp-txt' ); ?></span> <?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['minPaymentText'] ); ?></div>
                    <?php } ?>

                </div>

            </header>


            <div class="entry-content" style="clear:both;" ><?php the_content(); ?></div>

            <main style="margin: 0px 10px;">
                <table>
                    <thead>
                        <tr>
                            <th class="service"><?php _e( 'Service', 'iwp-txt' ); ?></th>
                            <th><?php _e( 'Price', 'iwp-txt' ); ?></th>
                            <th><?php _e( 'QTY', 'iwp-txt' ); ?></th>
                            <th><?php _e( 'Total', 'iwp-txt' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach( $invoiceContent['lineItems']['iwp_invoice_name'] as $key => $lineItem ){ ?>
                            <tr>
                                <td class="service">
                                    <?php echo $invoiceContent['lineItems']['iwp_invoice_name'][$key]; ?><br />
                                    <span style="font-size: 0.8em;"><?php echo $invoiceContent['lineItems']['iwp_invoice_description'][$key]; ?></span>
                                </td>
                                <td class="price"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['lineItems']['iwp_invoice_price'][$key] ); ?></td>
                                <td class="qty"><?php echo $invoiceContent['lineItems']['iwp_invoice_qty'][$key]; ?></td>
                                <td class="total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['lineItems']['iwp_invoice_total'][$key] ); ?></td>
                            </tr>
                        <?php } ?>


                        <tr>
                            <td style="text-align: right;" colspan="3"><?php _e( 'Subtotal', 'iwp-txt' ); ?></td>
                            <td class="total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['subtotal'] ); ?></td>
                        </tr>
                        <?php if( ! empty( $invoiceContent['invoice_totals']['tax'] ) ) { ?>
                        <tr>
                            <td style="text-align: right;" colspan="3"><?php _e( 'Tax', 'iwp-txt' ); ?></td>
                            <td class="total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['tax'] ); ?></td>
                        </tr>
                        <?php } ?>
                        <tr class="hidden_total">
                            <td style="text-align: right;" colspan="3"><?php _e( 'Adjustments', 'iwp-txt' ); ?></td>
                            <td class="total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['adjustments'] ); ?></td>
                        </tr>
                        <?php if( $invoiceContent['invoice_totals']['discount'] > 0 ) { ?>
                        <tr>
                            <td style="text-align: right;" colspan="3"><?php _e( 'Discount', 'iwp-txt' ); ?></td>
                            <td class="total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['discount'] ); ?></td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td style="text-align: right;" colspan="3" class="grand_total"><?php _e( 'Total', 'iwp-txt' ); ?></td>
                            <td class="grand_total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['total'] ); ?></td>
                        </tr>
                        <?php if( $invoiceContent['invoice_totals']["payments"] > 0 ) { ?>
                        <tr>
                            <td style="text-align: right;" colspan="3"><?php _e( 'Payments', 'iwp-txt' ); ?></td>
                            <td class="total" style="text-align: right;"><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['payments'] ); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;" colspan="3"><?php _e( 'Remaining Balance', 'iwp-txt' ); ?></td>
                            <td class="total" style="text-align: right;"><strong><?php echo $iwp_currency . ' ' . iwp_format_amount( $invoiceContent['invoice_totals']['total']= $invoiceContent['invoice_totals']['total'] - $invoiceContent['invoice_totals']['payments'] ); ?></strong></td>
                        </tr>
                        <?php } ?>

                    </tbody>
                </table>
                <div id="notices" class="entry-content">
                    <div class="notice"><?php _e( 'Notices', 'iwp-txt' ); ?>:
                        <?php if ( ! empty( $invoiceContent['invoice_notice'] ) ) : ?>
                            <?php echo $invoiceContent['invoice_notice']; ?>
                        <?php else: ?>
                            <p><em><?php _e( 'No notices for this Invoice', 'iwp-txt' ); ?></em></p>
                        <?php endif; ?>

                    </div>
                </div>
            </main>
            <footer class="entry-footer">
                <?php if ( ! empty( $iwp_options['invoice_global_notice'] ) ) : ?>
                    <?php echo $iwp_options['invoice_global_notice']; ?>
                <?php endif; ?>

                <?php do_action( 'iwp_payment_methods'); ?>
            </footer>
        </article>
