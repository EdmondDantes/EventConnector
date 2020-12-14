Event Connector (event dispatcher)
====================

The Connector event model is a simplified version from
http://www.w3.org/TR/DOM-Level-3-Events/

Unlike W3C Connector, it does not use event type optimization,
and therefore the current implementation is not recommended in terms of performance,
if you only need to handle one type of event.

Connector also does not use event phases (compared to the W3C model).