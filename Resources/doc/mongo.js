// 
monthly_summary = function (month) { month = month-1; return db.operations.aggregate( [ { $project:  { _id:0, nicedate: 1, date: 1, amount: 1} }, { $match: { amount: { $lt: 0 }, nicedate: { $gte: new Date(2014,month,1), $lt: new Date(2014,month,31) } } }, { $group: { _id: '$date', total: { $sum: '$amount' }, history: { $push: '$amount' } } }, { $sort: { _id: 1 } } ] ); }


// 
db.operations.find( { date: '02/05/2014', amount: { $lt: 0 } } ).pretty();


// create nicedate & niceamount
db.operations.find().forEach( function(op) {
	// make a nice date from simple string "DD/MM/YYYY"
	if ( 'undefined' == typeof op.date ) {
		print(' ERROR date undefined ');
		print(op._id);
	} else {
		var adate = op.date.split('/'); 
		op.nicedate = new Date (adate[2], adate[1]-1, adate[0], 12, 00, 00);
	}

	// make a nice float amount from a simple string "XX,XX"
	if ( 'undefined' == typeof op.amount ) {
		print(' ERROR amount undefined ');
		print(op._id);
		return;
	} else if ( 'string' == typeof op.amount ) {
		var amount = op.amount.split(','); 
		op.niceamount = parseFloat(amount.join('.')); 
	} else {
		op.niceamount = parseFloat(op.amount);
	}
	db.operations.save(op);
} )

// create niceamount
db.operations.find( { amount: /,/ }).forEach( function(op) { 
	print(op.niceamount); 
	db.operations.save(op); print(op.niceamount); } )
