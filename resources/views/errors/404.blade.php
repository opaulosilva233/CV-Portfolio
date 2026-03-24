<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: ui-monospace, SFMono-Regular, "SF Mono", Menlo, Consolas, "Liberation Mono", monospace;
            font-size: 14px;
            line-height: 1.2;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            max-width: 72ch;
            margin: 0;
        }
        a {
            color: blue;
            text-decoration: underline;
        }
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #121212;
                color: #e0e0e0;
            }
            a {
                color: #8ab4f8;
            }
        }
    </style>
</head>
<body>
<pre>
Network Working Group                                 P. Silva (Server)
Request for Comments: 404                                    March 2026
Category: Informational


                           Not Found (404)

Status of this Memo

   This memo provides information for the Internet community.  It does
   not specify an Internet standard of any kind.

Abstract

   This document describes the 404 Not Found error state, indicating
   that the server cannot find the requested resource.

1.  Introduction

   The client has communicated with the server, but the server could not
   find what was requested. In this server, it typically means the URI
   does not map to an existing document.

2.  Requested Resource

   The resource requested by the client could not be located.
   Please check the spelling of the URL or return to the main page.

3.  Resolution Action

   The user SHOULD attempt to navigate back to the homepage or use
   the provided navigation links to find the desired content.

   [ <a href="/">Return to Homepage (/)</a> ]

Author's Address

   Paulo Silva
   CV-Portfolio Server
   Internet: Administrator
</pre>
</body>
</html>
