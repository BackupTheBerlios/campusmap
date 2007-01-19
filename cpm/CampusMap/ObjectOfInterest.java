package CampusMap;

import objLoader.OBJModel;
import processing.core.PApplet;

/** Class describing main funcionality of the objects on the surface
 *  which are selectable for further informations
 *
 */
public class ObjectOfInterest{

	// static and final Definitions

	protected int currentDetailLevel;
	protected int oldDetailLevel;
	protected boolean selectable;
	protected boolean drawLines;
	protected boolean zFade;
	private boolean notifying;               // notifying initial drawing!!
	protected FVector myPos;
	protected FVector myRot;
	protected FVector myScale;
	protected CampusMap myParentApplet;
	protected String[] modelsToLoad;
	protected OBJModel myModels[];
	protected boolean modelsLoaded[] = {false, false, false};
	protected boolean wholeObjectIsInited;
	protected boolean modelsBeingLoaded[];
	protected String modelName;
	protected String address;
	protected float myAlpha = 1.0f;
	protected boolean drawingActive = true;

	protected boolean hideOnTour = false;

	// detail-constants
	static final int OVERVIEW = 0, CLOSER = 1, SELECTED = 2;
	final static String[] lods = {"OVERVIEW", "CLOSER", "SELECTED"};

	// distance at which to force a detail-change
	static final int CLOSER_DISTANCE = 400;
	static final int DETAIL_DISTANCE = 100;

	public ObjectOfInterest(){/**/}

	public ObjectOfInterest(CampusMap drawApplet, FVector p_myPos, FVector p_myScale, FVector p_myRot, String[] p_modelsToLoad, String p_address, boolean p_selectable, boolean p_drawLines, boolean p_zFade){
		myParentApplet = (CampusMap)drawApplet;
		myParentApplet.env.objectInitDisplay.setText("ObjectsOfInterest");
		selectable = p_selectable;
		drawLines = p_drawLines;
		zFade = p_zFade;
		notifying = false;

		//oldDetailLevel=-1;
		currentDetailLevel=OVERVIEW;
		this.myPos		= p_myPos;
		this.myScale	= p_myScale;
		this.myRot		= p_myRot;
		modelsToLoad	= p_modelsToLoad;
		address			= p_address;
		myModels		= new OBJModel[modelsToLoad.length];
		modelsBeingLoaded = new boolean[modelsToLoad.length];
		wholeObjectIsInited=false;
		for (int i = 0; i< modelsToLoad.length; i++)
		{
			modelsLoaded[i] = false;
			modelsBeingLoaded[i] = false;
		}
	}

	public int getNumberOfLodModels() {
		return myModels.length;
	}

	public boolean getLodModelLoaded(int lod) {
		if (lod > (getNumberOfLodModels()-1) || modelsLoaded[lod])
			return true;
		return false;
	}

	public void setLodModelLoaded(int modelNo) {
		modelsLoaded[modelNo] = true;
		modelsBeingLoaded[modelNo] = false;
//		System.out.println("Model "+modelsToLoad[modelNo]+" is loaded.");
	}

	public void setLodModelLoadFailed(int modelNo) {
		modelsLoaded[modelNo] = false;
		modelsBeingLoaded[modelNo] = false;
		System.out.println("Modelloading has failed for "+modelsToLoad[modelNo]+".");
	}

	public void notifyInitedObj(int lodNum){
		System.out.println("request start draw for "+modelsToLoad[lodNum]);
		notifying=true;
	}


	public boolean getLodModelBeingLoaded(int lod) {
		if (lod > (getNumberOfLodModels()-1) || modelsBeingLoaded[lod])
			return true;
		return false;
	}

	public void setLodModelBeingLoaded(int lod, boolean beingLoaded) {
		if (lod <= (getNumberOfLodModels()-1))
			modelsBeingLoaded[lod] = beingLoaded;

	}

	// Model loading
	public void loadModel(int lod) throws Exception{
		try{
			myModels[lod] = new OBJModel(myParentApplet, this, lod);
                        myModels[lod].setParentApplet(myParentApplet);
			myModels[lod].load(address, modelsToLoad[lod]);
			//myModels[modelNo].load(myParentApplet.getClass().getResourceAsStream("data/"+modelsToLoad[modelNo]));
			myModels[lod].drawMode(processing.core.PConstants.TRIANGLES);
			myModels[lod].setLineDrawing(drawLines);
			myModels[lod].setZFade(zFade);
			myModels[lod].setFadeMidPoint(new float[]{0.0f, 0.0f, 0.0f});
		}catch(ArrayIndexOutOfBoundsException e){
			System.err.println("not so many models for this object: ");
			e.printStackTrace();
		}
	}

	// draw method
	public void draw(PApplet myDrawApplet, boolean drawGrey){
		// drawing of the instance
		if (drawingActive && modelsLoaded[currentDetailLevel]) {
			myDrawApplet.pushMatrix();
			myDrawApplet.translate(myPos.getX(), myPos.getY(), myPos.getZ());
			myDrawApplet.scale(myScale.getX(), myScale.getY(), myScale.getZ());
			myDrawApplet.rotateX(myRot.getX());
			myDrawApplet.rotateY(myRot.getY());
			myDrawApplet.rotateY(myRot.getZ());

			myModels[currentDetailLevel].draw(myAlpha, drawGrey);
			myDrawApplet.popMatrix();

			//First time drawing invokes the showing.
			if(wholeObjectIsInited==false)wholeObjectIsInited=true;
		}
	}

	public void setAlpha(float alpha) {
		myAlpha = alpha;
	}

	/**
	 * actual Modelloading at detailchange
	 * to be overwritten
	 */
	public void changeModelDetail(){}

	// methods to change or obtain the level of detail
	public void setDetailLevel(int p_detailLevel){
		currentDetailLevel = p_detailLevel;
		System.out.println("detaillevel at:"+currentDetailLevel);
	}
	public int getDetailLevel(){
		return currentDetailLevel;
	}

	/**
	 *  method called when selected(rollover and click once),
	 *  excludes doubleclick,
	 *  overwritten by specialisations
	 */
	public void click(){
		// to be overwritten
	}

} // end ObjectsOfIterest


