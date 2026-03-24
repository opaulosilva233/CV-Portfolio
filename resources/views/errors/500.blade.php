<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Internal Server Error</title>
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
Request for Comments: 500                                    March 2026
Category: Informational


                     Internal Server Error (500)

Status of this Memo

   This memo provides information for the Internet community.  It does
   not specify an Internet standard of any kind.

Abstract

   This document describes the 500 Internal Server Error state, indicating
   that the server encountered an unexpected condition that prevented it
   from fulfilling the request.

1.  Introduction

   The server encountered an unexpected condition that prevented it from
   completing the request. This is typically a failure on the server side
   rather than a client error.

2.  Technical Details

   The server was unable to process the request due to an internal error.
   The system administrator has been notified (or will be shortly) by
   application logs.

   Error Details (if available):
   {{ $exception->getMessage() ?: 'Internal Exception' }}

3.  Resolution Action

   The user SHOULD try the request again later. Alternatively, the user
   MAY attempt to navigate back to the homepage.

   [ <a href="/">Return to Homepage (/)</a> ]

Author's Address

   Paulo Silva
   CV-Portfolio Server
   Internet: Administrator
</pre>
</body>
</html>
