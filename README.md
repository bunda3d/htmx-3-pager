# Dead Simple 3-Pager Website App (with API & Database)

Live Demo: [krisbunda.com/_apps/simple-3-pager](https://krisbunda.com/_apps/simple-3-pager/)
Project Blog: [krisbunda.com/blog/a-dead-simple-web-app-for-small-projects/](https://krisbunda.com/blog/2026/10/08/a-dead-simple-web-app-for-small-projects/)

## How did this start?

I read an [article about JS library "htmx"](https://www.infoworld.com/article/4221778/get-started-with-htmx-4.html), which provides short code to insert into HTML markup to perform API actions; i.e., GET data from a DB.

My initial reaction was 'Why would anyone need this when they can just write JavaScript AJAX calls?" I suppose it makes the HTML markup cleaner, but is that it? AND htmx expects API responses to be formatted in HTML (not JSON), so that's a possible friction point...

## Playing Devil's Advocate (with Myself)

As I thought more about it, my internal monologue morphed into a dialogue, with the new voice playing Devil's Advocate. It was surfacing compelling use cases for building a very lightweight, responsive frontend using htmx to call a php script serving as a micro backend... I began to see how this could do--with a handful of files and the simplest of tech stacks--what it takes so many more files, libraries, dependencies, technologies to do with, say, Angular or React. Or any popular web framework, really.

## Use Case

### As a [ blank ] I Want a Simple Dynamic Site

The "blank" in the Use Case statement could be along the lines of:

- Small Business Owner
- Project Organizer
- Single (or Limited) Product Seller

I realized a common use case could be served with a very simple, low-cost website that still provides dynamic features like data persistence and user interactivity (i.e., contact forms). And because of the simplicity, it should be maintainable by any web dev.

## Proof of Concept (PoC)

This repo is a 'three pager' proof of concept to demo how simple a dynamic, responsive website can be. it consists of less-than a dozen files, including the database, styling, and server config files.

I added the htmx and Bootstrap libraries to this PoC.

- htmx to simplify data operations (yes, writing out JS functions would also work)
- bootstrap to simplify styling and make the app responsive (appear usable on any screen format, from mobile to monitor).

All other elements of the tech stack are basic, ubiquitous and therefore highly maintainable and implementable.

## Check out a live demo

[https://krisbunda.com/_apps/simple-3-pager/#home](https://krisbunda.com/_apps/simple-3-pager/#home)

### Auth info

An "Admin Dashboard" feature is included with this **[PoC](# "Proof of Concept")**, along with a basic auth scheme (*enter `admin` & `pw` in the demo's auth form to access the dashboard*).

In case this demo looks useful and you've cloned the repo to make this app your own, below are details for legitimizing the auth scheme instead of it being a mere demo bauble.

#### Demo login

The admin demo uses the public credentials `admin` / `pw` to demonstrate PHP sessions and protected routes. This is not suitable for protecting real information.

#### Using OIDC

For a real deployment, register a Web application OAuth client with an identity provider such as Google. Configure the exact callback URL, then use a maintained PHP OIDC/OAuth client library in `api/auth.php` to redirect to the provider and handle its callback. Validate the callback’s `state` and the ID token, then map an approved identity to the app’s admin session. Keep protecting both the admin page route and `admin_data`.

Google’s [OpenID Connect guide](https://developers.google.com/identity/openid-connect/openid-connect) explains client registration, redirect URIs, the server flow, and token validation. Google recommends a client library for verification; see [Verify Google ID tokens on your server](https://developers.google.com/identity/gsi/web/guides/verify-google-id-token).

OIDC uses a client ID and, for a confidential server-side client, a client secret; these are not API keys. Configure secrets privately in the hosting control panel or server configuration. Do not commit real credentials to this repository. Google’s Sign in button/Identity Services is another option, but the ID token still has to be verified server-side.
