# Project Plan

## What problem are we trying to solve?

99% of the work in creating an api is bootstrapping an application and doing 
general CRUD operations. I'd like to be able to quickly create the baseline for 
an API by creating entities and fields in a friendly user interface.
When more complicated work is needed, it should be easy to extend functionality.

## How will we solve this problem?

A/C will allow a web-friendly admin to set up the application, create users and roles, entities and fields.
These elements will be available from standard restful endpoints.
An emphasis will be put on doing the grunt work but not overstepping bounds.
It should be up to a developer to extend functionality when necessary, and the process should be straight forward.

## Credo

- Never forget about the users. 
- Get feedback often. 
- Always consider best practice. 
- Don't try to do too much.

## Must haves

- Easily add new field types
- Allow for developers to replace routes or prepend and append routes with logic to add functionality.
- Never break abstraction and allow developers to replace adapters at bootstrap.