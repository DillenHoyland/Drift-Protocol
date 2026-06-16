import * as d3 from 'https://cdn.jsdelivr.net/npm/d3@7/+esm';

export default class LineChart{	
	// ID value for elements requiring UIDs on page
	#keyID;
	// elements
	#chartContainer;
	#chartSvg;
	#chartArea;
	#distLine;
	#markers;
	#reqLineSuccess;
	#reqLinePartial;
	#stdDevLine;
	#meanLine;
	#axisX;
	#axisY;
	#stdDevLblNeg;
	#stdDevLblPos;
	#meanLbl;
	// chart properties
	#margin;
	#height;
	#width;
	#axesOn;
	// mapping data
	#scaleX;
	#scaleY;
	#yMax;
	#yMaxPadding;
	#xMax;
	// data
	#data;
	#requirements;
	#mean;
	#stdDev;
	
	// when instantiated, use parameters to construct an SVG and g ('chart area') element
	// selection is string, width/height integers, gTransform a 4-element array top/right/bot/left
	constructor(selection, width, height, gTransform) {
		this.#chartContainer = d3.select(selection);
		this.#height = height;
		this.#width = width;
		this.#margin = gTransform;
		this.#chartSvg = this.#chartContainer
						.append('svg')
						.attr('width', width)
						.attr('height', height)
						.classed('LineChart', 'true');
		this.#chartArea = this.#chartSvg
						.append('g')
						.attr('fill', 'white')
						.attr('stroke', 'white')
						.attr('stroke-width', 1)
						.attr('transform', `translate(${this.#margin[3]},${this.#margin[0]})`);
		this.#distLine = this.#chartArea.append('path')
						.classed('distLine', 'true');
		this.#reqLineSuccess = this.#chartArea.append('line')
						.classed('reqLineSuccess', 'true');
		this.#reqLinePartial = this.#chartArea.append('line')
						.classed('reqLinePartial', 'true');
		this.#stdDevLine = this.#chartArea.append('line')
						.classed('stdDevLine', 'true');
		this.#meanLine = this.#chartArea.append('line')
						.classed('meanLine', 'true');
		this.#stdDevLblNeg = this.#chartArea.append('text')
						.classed('stdDevLblNeg', 'true');
		this.#stdDevLblPos = this.#chartArea.append('text')
						.classed('stdDevLblPos', 'true');
		this.#meanLbl = this.#chartArea.append('text')
						.classed('meanLbl', 'true');
	}
	
	// when called on some data, bind data to the chart area and create rects based on data
	render(key, data, requirements, axes, mean, stdDev, yMax, yMaxPadding, xMax) {
		
		// SETUP SCALES & DATA 
		// note data is just array of y-values
		this.#keyID = key;
		this.#data = data;
		this.#yMax = yMax + yMaxPadding;
		this.#yMaxPadding = yMaxPadding;
		this.#xMax = xMax;
		this.#mean = mean;
		this.#stdDev = stdDev;
		this.#axesOn = axes;
		
		// domain to yMax/xMax
		// the same yMax/xMax is provided to all graphs on the screen, so graphs have same scale and are visually comparable
		this.#scaleY = d3.scaleLinear()
						.domain([0, this.#yMax])
						.range([this.#height - this.#margin[0] - this.#margin[2], 0])
						.nice();
		
		// we take index as x values
		this.#scaleX = d3.scaleLinear()
						.domain([0, this.#xMax])
						.range([0, this.#width - this.#margin[3] - this.#margin[1]])
						.nice();
						
						
		// REQUIREMENTS LINES:
		this.#requirements = requirements;
		// need to also declare requirements at wider scope to access within e.g. "fill" functions below
		var tmpReqs = requirements;
		
		this.#reqLineSuccess = this.#chartArea.selectAll('line.reqLineSuccess')
								.attr('y1', 0)
								.attr('y2', this.#height - this.#margin[0] - this.#margin[2])
								.attr('x1', this.#scaleX(this.#requirements[0]) - ((this.#width - this.#margin[3] - this.#margin[1])/(2*this.#data.length)))
								.attr('x2', this.#scaleX(this.#requirements[0]) - ((this.#width - this.#margin[3] - this.#margin[1])/(2*this.#data.length)))
								.attr('stroke-width', 2)
								.attr('stroke', '#8439ED'); // tertiary colour/success
								
		this.#reqLinePartial = this.#chartArea.selectAll('line.reqLinePartial')
								.attr('y1', 0)
								.attr('y2', this.#height - this.#margin[0] - this.#margin[2])
								.attr('x1', this.#scaleX(this.#requirements[1]) - ((this.#width - this.#margin[3] - this.#margin[1])/(2*this.#data.length)))
								.attr('x2', this.#scaleX(this.#requirements[1]) - ((this.#width - this.#margin[3] - this.#margin[1])/(2*this.#data.length)))
								.attr('stroke-width', 2)
								.attr('stroke', '#FF006F'); // alarm colour/failure
		
		// MARKER BUBBLES:
		
		this.#markers = this.#chartArea.selectAll('circle.marker')
						.data(this.#data)
						.join('circle')
						.classed('marker', 'true')
						.attr('cx', (d,i)=>this.#scaleX(i))
						.attr('cy', d=>this.#scaleY(d))
						.attr('r', this.#height/40)
						// below - don't show markers for 0-points, else style/colour based on requirements
						.style('fill', function(d, i) { 
													if (i < tmpReqs[1]) { return '#FF006F'; } // alarm colour/failure
													else if (i >= tmpReqs[0]) { return '#00D9FF'; } // secondary colour/success
													else return '#8439ED'; // tertiary colour/partial
													})
						.style('stroke', '#00D9FF') // secondary colour
						.style('stroke-width', function (d, i) {
													if (d == 0) { return '0' } // invisible
													else return 2;
													})
						.attr('display', function(d,i) {
													if (d == 0) { return 'none'; } // render any 0-points invisible!
													else return 'initial';
													});
								
		
		// DISTRIBUTION LINE:

		// render line
		let lineGen = d3.area()
						.x((d,i) => this.#scaleX(i))
						.y1(d => this.#scaleY(d))
						.y0(this.#height - this.#margin[0] - this.#margin[2])
						.defined((d, i) => d != 0) // stops graphs showing 0-points
						.curve(d3.curveMonotoneX); // Smooths the line
						
				
		this.#distLine = this.#chartArea.selectAll('path.distLine')
						.datum(this.#data)
						.attr('d', lineGen)
						.attr('fill', '#00D9FF') // secondary colour
						.attr('fill-opacity', 0.3)
						.attr('stroke', '#00D9FF') // secondary colour
						.attr('stroke-width', 2);

		
		// AXES:
		if (this.#axesOn == true) {
			this.#axisX = this.#chartSvg
								.append('g')
								// note: +5 to add a small bit of padding between graph and x-axis
								.attr('transform', `translate(${this.#margin[3]},${this.#height - this.#margin[2] + 5})`);
			this.#axisY = this.#chartSvg
								.append('g')
								.attr('transform', `translate(${this.#margin[3]},${this.#margin[0]})`);
		
			let xAxis = d3.axisBottom(this.#scaleX),
				yAxis = d3.axisLeft(this.#scaleY);
			xAxis.tickValues([0, this.#scaleX.domain()[this.#scaleX.domain().length - 1]])
			yAxis.tickValues([0, this.#scaleY.domain()[this.#scaleY.domain().length - 1]])
			yAxis.tickFormat(y => y*100+"%");
			this.#axisX.call(xAxis)
						.style('font-size', '0.8rem')
						.style('font-family', 'Inter')
						.attr('stroke', '#00D9FF'); // secondary colour
			this.#axisX.select('.domain')
						.attr('stroke', '#00D9FF'); 
			this.#axisX.selectAll('.tick line')
						.attr('stroke', '#00D9FF');
			this.#axisY.call(yAxis)
						.style('font-size', '0.8rem')
						.style('font-family', 'Inter')
						.attr('stroke', '#00D9FF');
			this.#axisY.select('.domain')
						.attr('stroke', '#00D9FF');
			this.#axisY.selectAll('.tick line')
						.attr('stroke', '#00D9FF');

		}


		// STANDARD DEVIATION LINE & ARROWS:
		if (this.#stdDev != null && this.#mean != null) {
			// add defs to svg in order to add markers - credit to JSBob on Stackexchange @ https://stackoverflow.com/a/36579541
			this.#chartSvg.append("svg:defs")
							.append("svg:marker")
							.classed("stdDevArrowhead", "true")
							.attr("id", `stdDevArrow${this.#keyID}`)
							.attr("viewBox", "0 0 12 12")
							.attr("refX", 6)
							.attr("refY", 6)
							.attr("markerWidth", 4)
							.attr("markerHeight", 4)
							.attr("orient", "auto-start-reverse")
							.append("path")
							.attr("d", "M 0 0 12 6 0 12 3 6")
							.style("fill", "#FFFFFF");

			this.#stdDevLine = this.#chartArea.selectAll('line.stdDevLine')
								.attr('y1', this.#scaleY(this.#yMax - 0.7*this.#yMaxPadding)) // 30% of way between highest value on all graphs and top of graphs
								.attr('y2', this.#scaleY(this.#yMax - 0.7*this.#yMaxPadding)) // 30% of way between highest value on all graphs and top of graphs
								.attr('x1', this.#scaleX(this.#mean - this.#stdDev))
								.attr('x2', this.#scaleX(this.#mean + this.#stdDev))
								.attr('stroke-width', 2)
								.attr('stroke', '#FFFFFF')
								.attr('marker-end', `url(#stdDevArrow${this.#keyID})`)
								.attr('marker-start', `url(#stdDevArrow${this.#keyID})`);
			
			this.#meanLine = this.#chartArea.selectAll('line.meanLine')
								.attr('y1', this.#scaleY(this.#yMax - 0.6*this.#yMaxPadding)) // 10% of padding value above/below stddev line
								.attr('y2', this.#scaleY(this.#yMax - 0.8*this.#yMaxPadding)) // 10% of padding value above/below stddev line
								.attr('x1', this.#scaleX(this.#mean))
								.attr('x2', this.#scaleX(this.#mean))
								.attr('stroke-width', 2)
								.attr('stroke', '#FFFFFF');

			this.#stdDevLblNeg = this.#chartArea.selectAll('text.stdDevLblNeg')
								.attr('y', this.#scaleY(this.#yMax - 0.6*this.#yMaxPadding))
								.attr('x', this.#scaleX(this.#mean - this.#stdDev) - 10) // -10 so centre aligns with desired point, not left side
								.text('-σ')
								.style('font-family', 'Inter')
								.style('font-size', '0.8rem')
								.attr('stroke', '#FFFFFF');
			
			this.#stdDevLblPos = this.#chartArea.selectAll('text.stdDevLblPos')
								.attr('y', this.#scaleY(this.#yMax - 0.6*this.#yMaxPadding))
								.attr('x', this.#scaleX(this.#mean + this.#stdDev) - 10)
								.text('+σ')
								.style('font-family', 'Inter')
								.style('font-size', '0.8rem')
								.attr('stroke', '#FFFFFF');

			this.#meanLbl = this.#chartArea.selectAll('text.meanLbl')
								.attr('y', this.#scaleY(this.#yMax - 0.5*this.#yMaxPadding))
								.attr('x', this.#scaleX(this.#mean) - 5)
								.text('μ')
								.style('font-family', 'Inter')
								.style('font-size', '0.8rem')
								.attr('stroke', '#FFFFFF');

		}
	}
}