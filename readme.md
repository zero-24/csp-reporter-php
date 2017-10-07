# CSP Reporter

This is my small csp reporter script based on the script provided there https://mathiasbynens.be/notes/csp-reports

## Configuration

### Setup the script

- Download & copy the script to the webroot
- Edit the script and configure the "$recipients" and "$subject" variables
- Add to your CSP rule: report-uri /csp-reporter.php

### How the blacklist works

The backlist allows you to block reports from beeing send to you. For example the default backlist entry goes back to a issue in google chrome when you enabled the inbuild translation on your website. You as owner can not do anything here (as you would not whitelist that sources) but you also don't want to get mailed on any report like that.
When you have some things like that you can extend the blacklist if you want.

## Joomla Integration

If you need a Joomla Integration for CSP and other HTTP Headers you can take a look into my plugin: [plg_system_httpheader](https://github.com/zero-24/plg_system_httpheader)

