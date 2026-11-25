=== Rebel Studios Membership & Content Restriction ===
Contributors: rebelstudios
Tags: alumni directory, congregation, society members, church members, club membership
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Content restriction and free membership: make any page or post members only, with front-end sign-up, login and a member directory. No member limit.

== Description ==

Turn any WordPress site into a members area. Every page and post gets one of three settings — public, **logged-in users only**, or **members only** — and anyone who does not qualify reads your message where the content was. Visitors sign up and log in on the front end, and a **member directory** lists the people who have joined. All of it is three shortcodes and one box in the post editor.

Built to stay out of your way: no page builder lock-in, no account limits on the free plugin, and it never breaks your theme — restricted content is simply replaced with a message and a "Join" call to action.

= What the free plugin does =

* **Restrict any content.** Every page and post gets an "Access & Membership" box: leave it public, limit it to logged-in users, or limit it to members. Editors and admins always see everything.
* **Unlimited free members.** Register as many members as you like — no cap, no per-member fees.
* **Front-end registration:** `[rsmb_register]` drops a clean sign-up form on any page; new members get the Subscriber role and are flagged as members automatically.
* **Front-end login:** `[rsmb_login]` shows a login form so members never have to touch wp-admin.
* **Members directory:** `[rsmb_directory]` lists your members with avatars, and optional short bios.
* **Your branding, no code.** Set the members-only message, the accent color, and the redirect after joining from one settings screen.
* **Never breaks anything.** Restricted content is replaced with your message inside the normal page — no fatal errors, no white screens, no theme conflicts.

= Who it is for =

A members area does not have to be a business. This was built for the sites that need one and
nothing more: an alumni association listing the people who graduated with you, a church with a
section only its congregation can open, a club or society with a roster, an HOA with pages for
homeowners, a gym, a union local. Every one of those needs the same three things — pages that
only members see, a way for people to sign up without being handed a wp-admin login, and a
directory so they can find each other. That is the whole plugin.

Because members are unlimited and free, the size of your roll never changes what it costs you.

= Why this plugin =

Most membership plugins either cap your free members, bury basic content restriction behind a paywall, or take over your whole site with their own pages and templates. This one keeps the essentials free and works with the pages you already have.

= Pro =

[Pro](https://rebelstudiossoftware.com/membership-content-restriction) adds **paid memberships** sold through your own WooCommerce checkout, **access that expires** after a set number of days, and **membership tiers** you can restrict content to. The free plugin handles unlimited free members and content restriction on its own.

== Installation ==

1. Upload the plugin to `wp-content/plugins/` or install it from Plugins → Add New.
2. Activate it.
3. Go to **Membership** in the admin menu to set your members-only message and options.
4. Add the shortcodes `[rsmb_register]`, `[rsmb_login]` and `[rsmb_directory]` to pages, and restrict any page or post from the "Access & Membership" box in its editor.

== Frequently Asked Questions ==

= Does it limit how many members I can have? =

No. The free plugin supports unlimited members.

= Can I charge for membership? =

Charging for membership (through WooCommerce), expiring access and tiers are in Pro. The free plugin handles free members and content restriction.

= Does someone have to approve a new member? =

No, and it is worth knowing that before you publish the form. `[rsmb_register]` creates the account, marks it as a member and logs the person in, in one step: there is no approval queue and no email confirmation. The plugin sends no email when somebody joins, to them or to you. Anyone who can reach the page the form sits on can join from it, so if you need to vet people, keep the form on a page whose link you only hand out.

There is also no button in wp-admin that makes an existing WordPress user a member. Someone who already has an account becomes one by clicking "Become a member" themselves on the page carrying `[rsmb_register]`, and there is no bulk way to convert a list of existing users.

= What happens to restricted content for visitors who can't see it? =

The content is replaced, inside the normal page, with your members-only message and links to log in or join. The same check runs wherever the post can be read: the blog index, search, RSS and Atom feeds, embeds and the REST API. Its comments are hidden with it.

What stays public: the title, the date, the author and any featured image, so visitors can see what they would be joining for. Content that another plugin copies out of the post into its own pages or files is outside this plugin's reach.

= Can I restrict a PDF or an image? =

No. The "Access & Membership" box appears on posts, pages and any other public post type, but not on media, and the plugin never touches file URLs. A members-only newsletter uploaded to the media library still opens for anyone who has its link, even when the page you put it on is restricted. Protecting the file itself takes server rules this plugin does not write.

= Can I restrict a whole category, or the site in one go? =

No. Access is set one post or page at a time, in its own editor. There is no category or tag rule, no site-wide switch and no bulk edit, so a hundred members-only posts means a hundred boxes ticked. Editors and administrators always see everything either way.

= Will Google still index a members-only post? =

Yes, and it cuts both ways. The plugin sets no `noindex`: a search engine crawls the page logged out, like any other visitor, so it reads your members-only notice where the body should be. The title, date, author and featured image stay public, so the post can still be found by its title — but words only your members can read earn you no ranking. There is no setting here to hide restricted posts from search engines, and a post that was public before you restricted it keeps its old snippet until the page is recrawled.

So restrict the thing that is worth joining for, and leave the page that has to pull traffic public.

= What happens if I deactivate or delete the plugin? =

Deactivating unlocks everything immediately. The rules live on the posts themselves, and nothing enforces them while the plugin is off, so every members-only page reads as public again until you switch it back on — worth knowing before you deactivate to test something else. Nothing is lost: reactivate and the same pages are restricted again.

Deleting the plugin removes its settings — your members-only message, accent color, member label and redirect — and leaves each post's rule and each member's flag in the database. So a reinstall picks your roll and your restrictions up where they were. There is no uninstall sweep and no "make everything public" button: if you want the restrictions gone for good, set those posts back to Public before you delete.

= How many people does the members directory show? =

`[rsmb_directory]` lists up to 200 members, alphabetically by display name, with no paging; the Members screen in wp-admin lists up to 500. Membership itself is uncapped. It is these two lists that stop, so a roll of a few hundred outgrows the directory long before it outgrows the plugin.

= Does it work with my theme? =

Yes. It uses standard WordPress hooks and shortcodes and doesn't replace your theme's templates.

== Screenshots ==

1. The "Access & Membership" box on the post editor — public, logged-in users, or members.
2. The front-end registration form.
3. The members directory.
4. The settings screen.

== Other plugins by Rebel Studios ==

Focused WooCommerce plugins that each do one job well:

* **[Rebel Studios Event Tickets & QR Check-in](https://rebelstudiossoftware.com/wc-events-pro):** events, RSVP and QR tickets you scan at the door.
* **[Invoice Documents for WooCommerce](https://wordpress.org/plugins/rebel-studios-invoice-documents-woocommerce/):** automatic PDF invoices, packing slips and delivery notes.
* **[Brazilian Checkout Fields for WooCommerce](https://wordpress.org/plugins/rebel-studios-brazilian-checkout-fields/):** CPF/CNPJ validation and CEP autofill for Brazilian stores.
* **[Restaurant Menu & Reservations](https://wordpress.org/plugins/rebel-studios-restaurant-menu-reservations/):** a menu with allergen labels, table reservations and a printable QR code menu.

== Changelog ==

= 1.1.1 =
* Security fix: members-only posts could be read without an account through the REST API, RSS and Atom feeds, the blog index, search and embeds, because the check ran only on the post's own page. The rule is now decided by the post wherever it appears, including hand-written excerpts and Pro membership tiers.
* Security fix: comments on a restricted post were public. They now follow the post's rule.

= 1.1.0 =
* Improvement: the members-only notice leads with "Become a member", with "Log in" beside it for existing members.
* Improvement: redesigned join, login and directory cards; members without a Gravatar show their initials.
* Fix: the settings screen and this readme described Pro as having Stripe and content dripping. It has neither: Pro sells membership plans through your WooCommerce checkout, with expiry and tiers.
* Developer: `rsmb_directory_avatar_default` filter.

= 1.0.1 =
* Fix: "Join" on the members-only notice led to WordPress's registration screen, which is switched off on a default site; it now opens the page with the sign-up form.
* New: a logged-in visitor who is not yet a member can become one in one click (from the sign-up form or the members-only notice).
* Fix: the member directory listed people whose membership had lapsed.
* Improvement: the stylesheet now loads only where membership forms or restricted content appear.
* Fix: the Membership menu and its Members screen linked to a page that did not exist ("not allowed").

= 1.0.0 =
* Initial release: content restriction, front-end registration and login, members directory.
