Awesome Support WooCommerce (ASWC) provides the much-needed bridge between your WooCommerce store and the Awesome Support ticket system. The tight integration between the two is guaranteed to leave your customers feeling like their support experience is deeply rooted in their purchasing experience.

There is hardly any set-up required. Simply install the plugin and you're good to go.

## Installation

1. Buy the extension from Awesome Support!
2. Download the extension from your Awesome Support account
3. Go to **Plugins > Add New > Upload** and select the ZIP file you just downloaded
4. Click Install Now, and then click Activate
5. You should be ready!

## What It Does

ASWC does a few different things.

Firstly, users will now find that they can easily open a ticket from their WooCommerce 'My Account' page, for each of their orders:

![see screenshot](https://cloudup.com/c5OdnNeiW8J+)

Clicking **Get Help** will take them to the order page, where a ticket form awaits them. They can choose a specific product from the order that they need help with too:

![see screenshot](https://cloudup.com/cUfg8gF2neK+)

Back on the My Account page, they can also view their existing tickets, and easily choose one to check on or view again:

![see screenshot](https://cloudup.com/ckMs6TTalxw+)

The standard `[submit-ticket]` shortcode will continue to work as expected, with some extra fields added so the user can choose the product/order their ticket relates to:

![see screenshot](https://cloudup.com/cS1qrC_tLGm+)

Back in the admin view of a ticket, the agent will be able to see a new metabox called **Customer Profile**. This contains the ticket submitter's customer details, like so:

![see screenshot](https://cloudup.com/c7F9eGlipr6+)

And finally, in the admin view of an order, you can see the tickets that have been created relating to an order in the new **Order Tickets** metabox:

![see screenshot](https://cloudup.com/cVLniMaI5qT+)

## Settings

By default, WooCommerce Awesome Support limits the creation of new tickets to paying customers. However, you may want to allow non-customers to open a ticket.

To do so, go to **WooCommerce > Settings > Integration > Awesome Support** and enable the **Allow Non-Customers** setting like so:

![see screenshot](https://cloudup.com/cEkQaT0OiZp+)

## FAQ

**Why am I unable to see the 'New Ticket' form?**

Seeing something like this?

![see screenshot)(https://cloudup.com/cQnXXji2z4G+)

Probably because you're an admin or agent!

If you *really* want to see it, add the following to your `functions.php`:

```
add_filter( 'wpas_agent_submit_front_end', '__return_true' );
```