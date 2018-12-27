#! /usr/bin/env python
# -*- coding: utf-8 -*-

import os,sys
import urllib2
import cgi
from bs4 import BeautifulSoup
import json


def enja(term):

    url = "https://ejje.weblio.jp/content/" + term
    headers = {
        "User-Agent": "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:47.0) Gecko/20100101 Firefox/47.0"
    }

    req = urllib2.Request(url, headers=headers)
    res = urllib2.urlopen(req)
    html = res.read()
    res.close()

    soup = BeautifulSoup(html)

    try:
        lang = soup.find(class_="description").get_text().encode("utf-8")
        if lang == "主な意味":
            trans_word = soup.find(class_="Wejty").find_all("a")[0].get_text().encode("utf-8")
            if trans_word == "":
                trans_word = soup.find(class_="Pdqge").find_all("a")[0].get_text().encode("utf-8")
        elif lang == "主な英訳":
            trans_word = soup.find(class_="content-explanation").get_text().encode("utf-8")
        #meaning = soup.find(class_="addBody").get_text().encode("utf-8")
    except:
        trans_word = term
        

    return trans_word
    


#form = cgi.FieldStorage()
#term = form.getvalue("term", "0")

#print "Content-type: text/plain; charset='utf-8'\n"

term = sys.argv[1]

print enja(term)

