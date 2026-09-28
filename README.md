# Dead Simple 3-Pager Website App (with API & Database)
I read an article about JS library "htmx", it provides short code to insert into HTML markup to perform API actions like GET data from a DB. My knee-jerk reaction was 'Why would anyone need this when they can just write JavaScript AJAX calls?" I suppose it makes the HTML markup cleaner, but is that it? AND it expects API responses to be formatted in HTML (not JSON), so that's a possible friction point...

## Playing Devil's Advocate (with Myself)
As I thought about it more, my internal monologue morphed into a dialogue, with the new voice playing Devil's Advocate.

## Use Case: 
### As a [ blank ] I Want a Simple Dynamic Site.
I realized a common use case could be served with a very simple, low-cost website that still provides dynamic features like data persistence and user interactivity (i.e.; contact forms). And because of the simplicity, it should be maintainable by any web dev. 

The "blank" in the Use Case statement above could be along the lines of: Small Business Owner, Project Organizer, Single or Limited Product Seller, etc.

## Proof of Concept (PoC)
This repo is a 'three pager' proof of concept to demo how simple a dynamic, responsive website can be. it consists of less-than a dozen files, including the database, styling, and server config files. 

I added the htmx and bootstrap libraries to this PoC. 
- htmx to simplify data operations (yes, writing out JS functions would also work)
- bootstrap to simplify styling and make the app responsive (appear usable on any screen format, from mobile to monitor).
