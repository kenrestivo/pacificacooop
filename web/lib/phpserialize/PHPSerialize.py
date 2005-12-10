import types, string

"""
Serialize class for the PHP serialization format.

@version v0.3 BETA
@author Scott Hurring; scott at hurring dot com
@copyright Copyright (c) 2005 Scott Hurring
@license http://opensource.org/licenses/gpl-license.php GNU Public License
$Id$

Most recent version can be found at:
http://hurring.com/code/python/phpserialize/

Usage:
# Create an instance of the serialize engine
s = PHPSerialize()
# serialize some python data into a string
serialized_string = s.serialize(data)

Please see README.txt for more information.
"""

class PHPSerialize(object):
	"""
	Class to serialize data using the PHP Serialize format.

	Usage:
	s = PHPSerialize()
	serialized_string = s.serialize(data)
	"""

	def __init__(self):
		pass

	def serialize(self, data):
		return self._serialize(data)

	def _serialize(self, data):
		"""
		Serialize data recursively.
		Uses type() to figure out what type to serialize a thing as...
		"""

		# Integer => integer
		if type(data) is types.IntType:
			return "i:%s;" % data

		# Floating Point => double
		elif type(data) is types.FloatType or type(data) is types.LongType:
			return "d:%s;" % data

		# String => string
		elif type(data) is types.StringType:
			return "s:%i:\"%s\";" % (len(data), data);

		# None / NULL
		elif type(data) is types.NoneType:
			return "N;";

		# Tuple and List => array
		# The 'a' array type is the only kind of list supported by PHP.
		# array keys are automagically numbered up from 0
		elif type(data) is types.ListType or type(data) is types.TupleType:
			i = 0
			out = []
			# All arrays must have keys
			for k in data:
				out.append(self._serialize(i))
				out.append(self._serialize(k))
				i += 1
			return "a:%i:{%s}" % (len(data), "".join(out))

		# Dict => array
		# Dict is the Python analogy of a PHP array
		elif type(data) is types.DictType:
			out = []
			for k in data:
				out.append(self._serialize(k))
				out.append(self._serialize(data[k]))
			return "a:%i:{%s}" % (len(data), "".join(out))

		# Boolean => bool
		elif type(data) is types.BooleanType:
			if data: b = 1
			else: b = 0
			return "b:%i;" % (b)

		# I dont know how to serialize this
		else:
			raise Exception("Unknown / Unhandled data type (%s)!" % type(data))
