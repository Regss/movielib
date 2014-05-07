# -*- coding: utf-8 -*-

import re
import os
import hashlib

class HASH:

    def __init__(self):
        self.path = os.path.dirname(os.path.abspath(__file__)).replace('\\', '/')
        self.getFiles()
        wait = raw_input('Dowlony klawisz...')
        
    def getFiles(self):
        fileTable = []
        dir = os.listdir(self.path)
        for file in dir:
            if file[-3:] == 'php':
                fileTable.append([self.path, file])
        
        dir = os.listdir(self.path + '/js')
        for file in dir:
            if file[-2:] == 'js':
                fileTable.append([self.path, 'js/' + file])
        self.getHash(fileTable)
        
    def getHash(self, fileTable):
        toWrite = []
        for file in fileTable:
            md5 = hashlib.md5(open(file[0]+'/'+file[1], 'rb').read()).hexdigest()
            toWrite.append(file[1] + ':' + md5)
        
        fp = open(self.path + '/md5.checksum', 'w')
        fp.write(';'.join(toWrite))
        fp.close()
        
HASH()