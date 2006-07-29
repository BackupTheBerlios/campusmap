package CampusMap;


/** Class describing a human that moves around on the groundplane
 * 
 */

import java.util.Vector;
import processing.core.*;

public class Bus extends MovingObject{
	
	protected	final static int MOVE_Z 		= 20;
	private		final static int BUS_LENGTH		= 90;
	private		final static int BUS_WIDTH		= 18;
	private		final static int BUS_HEIGHT		= 16;
	private		final static int TOP_SPEED		= 100;
	private FVector myNosePos;
	private int		myNoseTarget;
	private FVector myTailPos;
	private int		myTailTarget;
	private int		myRouteNo;
	private String	myBusNumber;
	
	private int		stopTime;
	
	//all 4 bus routes with all the coordinates
	private static FVector routes[][] = {	{	//stadt -> uni
												new FVector(3634.9783f, 639.94275f, 0),
												new FVector(1098.045f, 544.46716f, 0),
												new FVector(369.8719f, 539.14795f, 0),
												new FVector(321.9108f, 516.13336f, 0),
												new FVector(44.96823f, 537.56604f, 0),
												new FVector(137.21411f, 3726.3037f, 0),
											},
											{	//uni -> stadt
												new FVector(137.21411f, 3726.3037f, 0),
												new FVector(44.96823f, 537.56604f, 0),
												new FVector(272.14615f, 550.1121f, 0),
												new FVector(364.92474f, 572.775f, 0),
												new FVector(425.9287f, 548.39624f, 0),
												new FVector(1098.045f, 544.46716f, 0),
												new FVector(3634.9783f, 639.94275f, 0),
											},
											{	//stadt -> hochschulstadtteil
												new FVector(3634.9783f, 639.94275f, 0),
												new FVector(1098.045f, 544.46716f, 0),
												new FVector(369.8719f, 539.14795f, 0),
												new FVector(321.9108f, 516.13336f, 0),
												new FVector(-806.77216f, 537.2142f, 0),
												new FVector(-988.994f, 362.39578f, 0),
												new FVector(-997.1182f, 28.302902f, 0),
												new FVector(-1072.3102f, -83.532074f, 0),
												new FVector(-1084.3718f, -146.60031f, 0),
												new FVector(-4259.3506f, -4835.6978f, 0),
											},
											{	//hochschulstadtteil -> stadt
												new FVector(-4259.3506f, -4835.6978f, 0),
												new FVector(-1132.1445f, -135.03197f, 0),
												new FVector(-1072.3102f, -83.532074f, 0),
												new FVector(-997.1182f, 28.302902f, 0),
												new FVector(-988.994f, 362.39578f, 0),
												new FVector(-806.77216f, 537.2142f, 0),
												new FVector(272.14615f, 550.1121f, 0),
												new FVector(364.92474f, 572.775f, 0),
												new FVector(425.9287f, 548.39624f, 0),
												new FVector(1098.045f, 544.46716f, 0),
												new FVector(3634.9783f, 639.94275f, 0),
											},
										};
	
	//wait times of the buses per route coordinate
	private static int waitTimes[][] = {	{	0,
												0,
												0,
												15000,
												0,
												0,
											},
											{	0,
												0,
												0,
												15000,
												0,
												0,
												0,
											},
											{	0,
												0,
												0,
												15000,
												0,
												0,
												0,
												0,
												15000,
												0,
											},
											{	0,
												15000,
												0,
												0,
												0,
												0,
												0,
												15000,
												0,
												0,
												0,
											}
										};
	
	//start times of the buses per route
	private static int startTimes[][] = {	{2, 12, 22, 42, 52, 62, 82, 92, 102},	//stadt -> uni
											{3, 13, 33, 43, 53, 73, 83, 93, 113},	//uni -> stadt
											{32, 72, 112},							//stadt -> hochschulstadtteil
											{23, 3, 103}							//hochschulstadtteil -> stadt
										};
	
	//bus numbers for the start times
	private static int busNumbers[][] = {	{9, 19, 9, 9, 19, 9, 9, 19, 9},	//stadt -> uni
											{19, 9, 9, 19, 9, 9, 19, 9, 9},	//uni -> stadt
											{19, 19, 19},							//stadt -> hochschulstadtteil
											{19, 19, 19}							//hochschulstadtteil -> stadt
										};
	
	//constructor
	public Bus(CampusMap drawApplet, int routeNo, int busNumber) {
		super(drawApplet, new FVector(3));
		drawApplet.env.objectInitDisplay.setText("Busses");
		myRouteNo = routeNo;
		myBusNumber = "" + busNumber;
		if (myBusNumber.length()>1) {
			String tempNumber = new String(myBusNumber);
			myBusNumber = "" + myBusNumber.charAt(0);
			for (int i = 1; i< tempNumber.length(); i++) {
				myBusNumber = myBusNumber + "\n" + tempNumber.charAt(i);
			}
		}
		myNoseTarget = 1;
		myTailTarget = 1;
		myTailPos = routes[myRouteNo][0].add(calcDirection(1).multiply(BUS_LENGTH));
		myNosePos = routes[myRouteNo][0].add(calcDirection(1).multiply(BUS_LENGTH*2));
		setmiddlePosition();
		
		fullSpeed = TOP_SPEED;
		currentSpeed = fullSpeed;
		
	}
	
	// draw method
	public void draw(){
		myDrawApplet.noStroke();
		myDrawApplet.pushMatrix();
		myDrawApplet.translate(myPos.getX(), myPos.getY(), myPos.getZ());
		myDrawApplet.rotateZ(myDrawApplet.atan2(myDir.e[1], myDir.e[0]));
		//body
		myDrawApplet.stroke(180, 180, 230); 
		myDrawApplet.fill(150, 150, 200, 255);
		myDrawApplet.translate(0, 0, BUS_HEIGHT/2);
		myDrawApplet.box(BUS_LENGTH, BUS_WIDTH, BUS_HEIGHT);
		myDrawApplet.noStroke();
		//myDrawApplet.translate(-(BUS_LENGTH/2), -(2*BUS_WIDTH), BUS_HEIGHT+0.1f);
		myDrawApplet.translate((BUS_LENGTH/3), -(BUS_WIDTH/2), BUS_HEIGHT/2+0.01f);
		myDrawApplet.rotateZ(PConstants.PI/2);
		myDrawApplet.textFont(myDrawApplet.myFont, 26);
		myDrawApplet.fill(255, 255, 255, 255);
		myDrawApplet.text(myBusNumber, 0, 0, 70, 70); 
		myDrawApplet.popMatrix();
	}
	
	public void move(float partOfSecond, boolean doCheck) {
			FVector direction = calcDirection(myTailTarget);
			if (stopTime != 0) {
				if (currentSpeed > 0) {
					currentSpeed-= partOfSecond*TOP_SPEED/2;
					if (currentSpeed < 0)
						currentSpeed = 0;
				}
				else if (myDrawApplet.millis() > stopTime + waitTimes[myRouteNo][myNoseTarget-1])
					stopTime = 0;
			}
				
			else if (currentSpeed < TOP_SPEED) {
				currentSpeed+= partOfSecond*TOP_SPEED/3;
				if (currentSpeed > TOP_SPEED)
					currentSpeed = TOP_SPEED;
			}
			
				
				
			myTailPos.addMe(direction.multiply(currentSpeed*partOfSecond));
			int lastTarget = (myTailTarget == 0) ? routes[myRouteNo].length-1 : myTailTarget -1;
			if (routes[myRouteNo][lastTarget].subtract(myTailPos).magnitudeSqr() > routes[myRouteNo][lastTarget].subtract(routes[myRouteNo][myTailTarget]).magnitudeSqr()) {
				myTailTarget++;
				if (myTailTarget >= routes[myRouteNo].length)
					this.requestingMedicide = true;
				direction = calcDirection(myTailTarget);
				float distanceToLastTarget = routes[myRouteNo][myTailTarget-1].subtract(myTailPos).magnitude();
				myTailPos = routes[myRouteNo][myTailTarget-1].add(direction.multiply(distanceToLastTarget));
				//System.out.println("new Target tail: " + myTailTarget);
			}
			
			direction = calcDirection(myNoseTarget);
			myNosePos.addMe(direction.multiply(currentSpeed*partOfSecond));
			lastTarget = (myNoseTarget == 0) ? routes[myRouteNo].length-1 : myNoseTarget -1;
			if (routes[myRouteNo][lastTarget].subtract(myNosePos).magnitudeSqr() > routes[myRouteNo][lastTarget].subtract(routes[myRouteNo][myNoseTarget]).magnitudeSqr()){
				myNoseTarget++;
				if (myNoseTarget >= routes[myRouteNo].length)
					this.requestingMedicide = true;
				direction = calcDirection(myNoseTarget);
				float distanceToLastTarget = routes[myRouteNo][myNoseTarget-1].subtract(myNosePos).magnitude();
				myNosePos = routes[myRouteNo][myNoseTarget-1].add(direction.multiply(distanceToLastTarget));
				if (waitTimes[myRouteNo][myNoseTarget-1] > 0) {
					stopTime = myDrawApplet.millis();
				}
				//System.out.println("new Target nose: " + myNoseTarget);
			}
		
		
		setmiddlePosition();
		myDir = myNosePos.subtract(myTailPos).normalize();
		
	}
	
	private void setmiddlePosition() {
		myPos = myTailPos.add( myNosePos.subtract(myTailPos).multiply(0.5f) );
	}
	
	private FVector calcDirection(int targetNo) {
		while (targetNo >= routes[myRouteNo].length)
			targetNo--;

		int lastTarget = targetNo -1;
		if (targetNo == 0) lastTarget = routes[myRouteNo].length-1;
		//System.out.println(" " + targetNo + " " + lastTarget);
		
		return routes[myRouteNo][targetNo].subtract(routes[myRouteNo][lastTarget]).normalize();
	}
	
	public static int[] CREATE_BUSES_STARTING_AT(int hour, int minute, Vector addToThisVector, CampusMap applet) {
		if (hour%2==1)
			minute +=60;
		for (int routeNo = 0; routeNo < startTimes.length; routeNo++)
			for (int timeNo = 0; timeNo < startTimes[routeNo].length; timeNo++)
				if (minute == startTimes[routeNo][timeNo])
					addToThisVector.add(new Bus(applet, routeNo, busNumbers[routeNo][timeNo]));
		int[] buses = {2};
		return buses;
	}

	
}