import uno
from os.path import abspath, isfile, splitext
from com.sun.star.beans import PropertyValue
from com.sun.star.task import ErrorCodeIOException
from com.sun.star.connection import NoConnectException
from sys import argv
cSourceFile = argv[1] 
cSourceURL = uno.systemPathToFileUrl(abspath( cSourceFile ) )

cTargetFile = argv[2] 
cTargetURL = uno.systemPathToFileUrl(abspath(cTargetFile ) )

# Open the source document. 
# No filter necessary.  OOo will figure it out from the .DOC extension. 
#document = self.desktop.loadComponentFromURL(inputUrl, "_blank", 0, self._toProperties(Hidden=True))
oDoc = desktop.loadComponentFromURL( cSourceURL , "_blank", 0)

# Save the newly opened document. 
# Use a tupple of property values. 
# The only property value is the specification of what Filter to use for saving. 
oDoc.storeToURL( cTargetURL, (createPropertyValue("FilterName","writer_pdf_Export"),) )